<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Repository\PostRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

class CommentController extends AbstractController
{
    /**
     * @Route("/comment/create", name="create_comment", methods={"POST"})
     */
    public function createComment(Request $request, SerializerInterface $serializer, UserRepository $userRepository, PostRepository $postRepository): Response
    {
        $em = $this->getDoctrine()->getManager();

        $receivedJson = $request->getContent();

        //récupération des informations User et Post pour les relations entre les tables

        $userId = json_decode($receivedJson)->user_id;
        $user = $userRepository->find($userId);

        $postId = json_decode($receivedJson)->post_id;
        $post = $postRepository->find($postId);

        $comment = $serializer->deserialize($receivedJson, Comment::class, 'json');

        $comment->setCreationDate(new \DateTime());
        $comment->setUser($user);
        $comment->setPost($post);

        $em->persist($comment);
        $em->flush();

        return $this->json($comment, 201, [], ['groups' => 'comment:read']);
    }

    /**
     * @Route("/comment", name="get_all_comments", methods={"GET"})
     */
    public function getAllComments(): Response
    {
        $commentRepository = $this->getDoctrine()->getRepository(Comment::class);

        return $this->json($commentRepository->findBy([],['creation_date' => 'DESC']), 200, [], ['groups' => 'comment:read']);
    }

    /**
     * @Route("/comment/{id}", name="get_one_comment", methods={"GET"})
     */
    public function getOneComment(Int $id): Response
    {
        $commentRepository = $this->getDoctrine()->getRepository(Comment::class);

        return $this->json($commentRepository->find($id), 200, [], ['groups' => 'comment:read']);
    }

    /**
     * @Route("/comment/delete/{id}", name="delete_comment", methods={"DELETE"})
     */
    public function deleteComment(Int $id): Response
    {
        $em = $this->getDoctrine()->getManager();

        $comment = $this->getDoctrine()->getRepository(Comment::class)->find($id);

        if (!$comment) {
            throw $this->createNotFoundException(
                'Pas de résultat'
            );
        }

        $em->remove($comment);
        $em->flush();

        return $this->json(['message' => 'Commentaire supprimé'], 200, []);
    }
}
