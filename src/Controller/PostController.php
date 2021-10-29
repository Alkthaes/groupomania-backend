<?php

namespace App\Controller;

use App\Entity\Post;
use App\Repository\UserRepository;
use App\Utils\Base64FileExtractor;
use App\Utils\UploadedBase64File;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

class PostController extends AbstractController
{
    /**
     * @Route("/post/create", name="create_post", methods={"POST"})
     */
    public function createPost(Request $request, Base64FileExtractor $base64FileExtractor, UserRepository $userRepository): Response
    {
        $em = $this->getDoctrine()->getManager();

        $titleData = $request->request->get('title');

        $userId = $request->request->get('user_id'); //on récupère l'utilisateur pour la relation post-user
        $user = $userRepository->find($userId);

        $base64String = $request->request->get('image');
        $base64Image = $base64FileExtractor->extractBase64String($base64String);
        $imageFile = new UploadedBase64File($base64Image, 'image');

        $imgName = 'post_image'.'-'.uniqid().'.'.$imageFile->guessExtension();

        $imageFile->move(
            $this->getParameter('post_directory'),
            $imgName
        );

        $post = new Post();

        $post->setTitre($titleData);
        $post->setImage($this->getParameter('post_directory').`/$imgName`);
        $post->setUser($user);
        $post->setCreationDate(new \DateTime());

        $em->persist($post);
        $em->flush();
        return $this->json($post, 201, [], ['groups' => 'post:read']);
    }

    /**
     * @Route("/post", name="get_all_posts", methods={"GET"})
     */
    public function getAllPosts(): Response
    {
        $postRepository = $this->getDoctrine()->getRepository(Post::class);

        $posts = $postRepository->findBy([],['creation_date' => 'DESC']);

        return $this->json($posts, 200, [], ['groups' => 'post:read']);
    }

    /**
     * @Route("/post/{id}", name="get_one_post", methods={"GET"})
     */
    public function getOnePost(Int $id): Response
    {
        $postRepository = $this->getDoctrine()->getRepository(Post::class);

        $post = $postRepository->find($id);

        return $this->json($post, 200, [], ['groups' => 'post:read']);
    }

    /**
     * @Route("/post/delete/{id}", name="delete_post", methods={"DELETE"})
     */
    public function deletePost(Int $id): Response
    {
        $em = $this->getDoctrine()->getManager();

        $postRepository = $this->getDoctrine()->getRepository(Post::class);

        $post = $postRepository->find($id);

        if (!$post) {
            throw $this->createNotFoundException(
                'Aucun post trouvé !'
            );
        }

        $em->remove($post);
        $em->flush();

        return $this->json(['message' => 'Post supprimé'], 200, []);
    }
}
