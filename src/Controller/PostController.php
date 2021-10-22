<?php

namespace App\Controller;

use App\Entity\Post;
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
    public function createPost(Request $request): Response
    {
        $em = $this->getDoctrine()->getManager();

        $imgData = $request->files->get('image');
        $titleData = $request->files->get('title');
        $userId = $request->files->get('user_id');

        $imgName = 'post_image'.'.'.$imgData->guessExtension();

        $imgData->move(
            $this->getParameter('post_directory'),
            $imgName
        );

        $post = new Post();

        $post->setTitre($titleData);
        $post->setImage(`../../public/Images/PostImages/$imgName`);
        $post->setUser($userId);
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

        $posts = $postRepository->findAll();

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
