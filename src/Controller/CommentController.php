<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Repository\CommentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

class CommentController extends AbstractController
{
    /**
     * @Route("/comment", name="create_comment", methods={"POST"}
     */
    public function createComment(Request $request, SerializerInterface $serializer, EntityManagerInterface $em): Response
    {
        $receivedJson = $request->getContent();

        $comment = $serializer->deserialize($receivedJson, Comment::class, 'json');

        $comment->setCreationDate(new \DateTime());

        $em->persist($comment);
        $em->flush();

        return $this->json($comment, 201, [], ['groups' => 'comment:read']);
    }

    /**
     * @Route("/comment", name="get_all_comments", methods={"GET"})
     */
    public function getAllComments(CommentRepository $commentRepository): Response
    {
        return $this->json($commentRepository->findAll(), 200, [], ['groups' => 'comment:read']);
    }

    /**
     * @Route("/comment/{id}, name="get_one_comment", methods={"GET"})
     */
    public function getOneComment(Int $id, CommentRepository $commentRepository): Response
    {
        return $this->json($commentRepository->find($id), 200, [], ['groups' => 'comment:read']);
    }

    /**
     * @Route("/comment/delete/{id}, name="delete_comment", methods={"DELETE"})
     */
    public function deleteComment(Int $id, CommentRepository $commentRepository, EntityManagerInterface $em): Response
    {
        $comment = $commentRepository->find($id);

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
