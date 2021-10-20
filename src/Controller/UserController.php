<?php

// src/ControllerUserController.php
namespace App\Controller;

// ...
use App\Entity\User;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

class UserController extends AbstractController
{
    /**
     * @Route("/user/signup", name="create_user", methods={"POST"})
     */
    public function createUser(Request $request, SerializerInterface $serializer, UserPasswordHasherInterface $passwordHasher): Response
    {
        $em = $this->getDoctrine()->getManager();

        $userRepository = $this->getDoctrine()->getRepository(User::class);

        $receivedJson = $request->getContent();

        $user = $serializer->deserialize($receivedJson, User::class, 'json');

        //check if email has already been registered
        if ($userRepository->findOneBy(['email' => $user->getEmail()])) {
            return new JsonResponse(['message' => 'Adresse email déjà utilisée !'], 400, [], true);
        } else {
            $plaintextPassword = $user->getPassword();

            $hashedPassword = $passwordHasher->hashPassword(
                $user,
                $plaintextPassword
            );

            $user->setCreationDate(new \DateTime());
            $user->setPassword($hashedPassword);

            $em->persist($user);
            $em->flush();

            return $this->json($user, 201, [], ['groups' => 'user:read']);
        }


    }

    /**
     * @Route("/user/login", name="login_user", methods={"POST"})
     */

    public function logInUser(Request $request, SerializerInterface $serializer, UserPasswordHasherInterface $passwordHasher, JWTTokenManagerInterface $JWTManager): Response
    {

        $userRepository = $this->getDoctrine()->getRepository(User::class);

        $receivedJson = $request->getContent();

        $userInfos = $serializer->deserialize($receivedJson, User::class, 'json');

        $user = $userRepository->findOneBy(['email' => $userInfos->getEmail()]);

        $plainPassword = $userInfos->getPassword();

        if (!$user) {
            return new JsonResponse(['message' => 'Adresse email inconnue !'], 400, [], true);
        } else {
            if (!$passwordHasher->isPasswordValid($user, $plainPassword)) {
                return new JsonResponse(['message' => 'Mot de passe incorrect !', 400, [], true]);
            } else {

                $token = $JWTManager->create($user);

                return $this->json(['user' =>$user, 'token' => $token], 200, [], ['groups' => 'user:read']);
            }
        }


    }

    /**
     * @Route("/user", name="get_all_users", methods={"GET"})
     */
    public function getAllUsers(): Response
    {
        $userRepository = $this->getDoctrine()->getRepository(User::class);

        $users = $userRepository->findAll();

        return $this->json($users, 200, [], ['groups' => 'user:read']);
    }

    /**
     * @Route("/user/{id}", name="get_one_user", methods={"GET"})
     */
    public function getOneUser(int $id): Response
    {

        $userRepository = $this->getDoctrine()->getRepository(User::class);

        $user = $userRepository->find($id);

        $authenticationSuccesHandler = $this->container->get('lexik_jwt_authentication.handler.authentication_success');

        if (!$user) {
            throw $this->createNotFoundException(
                'Aucun utilisateur trouvé avec l\'identifiant' . $id
            );
        }

        return $this->json([$authenticationSuccesHandler->handleAuthenticationSuccess($user), $user], 200, [], ['groups' => 'user:read']);
    }

    /**
     * @Route("/user/delete/{id}", name="delete_user", methods={"DELETE"})
     */
    public function deleteUser(int $id): Response
    {
        $em = $this->getDoctrine()->getManager();

        $userRepository = $this->getDoctrine()->getRepository(User::class);

        $user = $userRepository->find($id);

        if (!$user) {
            throw $this->createNotFoundException(
                'Aucun utilisateur trouvé avec l\'identifiant' . $id
            );
        }

        $em->remove($user);
        $em->flush();

        return $this->json(['message' => 'Utilisateur supprimé'], 200, []);
    }
}
