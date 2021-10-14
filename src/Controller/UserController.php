<?php

// src/ControllerUserController.php
namespace App\Controller;

// ...
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

class UserController extends AbstractController
{
    /**
     * @Route("/user", name="create_user", methods={"POST"})
     */
    public function createUser(Request $request, SerializerInterface $serializer, EntityManagerInterface $em): Response
    {
        $receivedJson = $request->getContent();

        $user = $serializer->deserialize($receivedJson, User::class, 'json');

        $user->setCreationDate(new \DateTime());

        $em->persist($user);
        $em->flush();

        return $this->json($user, 201, [], ['groups' => 'user:read']);
    }

    /**
     * @Route("/user", name="get_all_users", methods={"GET"})
     */
    public function getAllUsers(UserRepository $userRepository):Response
    {
        $users = $userRepository->findAll();

        return $this->json($users, 200, [], ['groups' => 'user:read']);
    }

    /**
     * @Route("/user/{id}", name="get_one_user", methods={"GET"})
     */
    public function getOneUser(int $id, UserRepository $userRepository): Response {
        $user = $userRepository->find($id);

        if (!$user) {
            throw $this->createNotFoundException(
                'Aucun utilisateur trouvé avec l\'identifiant'.$id
            );
        }

        return $this->json($user, 200, [], ['groups' => 'user:read']);
    }

    /**
     * @Route("/user/delete/{id}", name="delete_user", methods={"DELETE"})
     */
    public function deleteUser(int $id, UserRepository $userRepository, EntityManagerInterface $em): Response
    {
           $user = $userRepository->find($id);

           if (!$user) {
               throw $this->createNotFoundException(
                   'Aucun utilisateur trouvé avec l\'identifiant'.$id
               );
           }

           $em->remove($user);
           $em->flush();

           return $this->json(['message' => 'Utilisateur supprimé'], 200, []);
    }
}
