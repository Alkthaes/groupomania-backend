<?php

// src/ControllerUserController.php
namespace App\Controller;

// ...
use App\Entity\User;
use App\Utils\Base64FileExtractor;
use App\Utils\UploadedBase64File;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
            $user->setRoles(['ROLE_USER']);
            $user->setPicture('C:\wamp64\www\groupomania-backend/public/Images/default-avatar.jpg');

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
     * @Route("/user/account/{id}", name="get_one_user", methods={"GET"})
     */
    public function getOneUser(int $id): Response
    {

        $userRepository = $this->getDoctrine()->getRepository(User::class);

        $user = $userRepository->find($id);

        if (!$user) {
            throw $this->createNotFoundException(
                'Aucun utilisateur trouvé avec l\'identifiant' . $id
            );
        }

        return $this->json($user, 200, [], ['groups' => 'user:read']);
    }

    /**
     * @Route("/user/update/avatar", name="update_avatar", methods={"PUT"})
     */
    public function updatePicture(Request $request, Base64FileExtractor $base64FileExtractor): Response
    {
        $em = $this->getDoctrine()->getManager();
        $userRepository = $this->getDoctrine()->getRepository(User::class);

        $userId = $request->toArray()['user_id'];

        $user = $userRepository->find($userId);

        $base64String = $request->toArray()['picture'];
        $base64Image = $base64FileExtractor->extractBase64String($base64String);
        $imageFile = new UploadedBase64File($base64Image, 'picture');

        $imgName = 'user_avatar'.'-'.uniqid().'.'.$imageFile->guessExtension();

        $imageFile->move(
            $this->getParameter('user_directory'),
            $imgName
        );

        $user->setPicture($this->getParameter('user_directory').'/'.$imgName);

        $em->persist($user);
        $em->flush();

        return $this->json($user, 200, [], ['groups' => 'user:read']);
    }

    /**
     * @Route("/user/update/infos", name="update_infos", methods={"PUT"})
     */
    public function updateInfos(Request $request, SerializerInterface $serializer, UserPasswordHasherInterface $passwordHasher): Response
    {
        $em = $this->getDoctrine()->getManager();

        $receivedJson = $request->getContent();
        $lastname = $request->toArray()['lastname'];
        $firstname = $request->toArray()['firstname'];
        $email = $request->toArray()['email'];
        $password = $request->toArray()['password'];
        $secteur = $request->toArray()['secteur'];
        $fonction = $request->toArray()['fonction'];


        $user = $serializer->deserialize($receivedJson, User::class, 'json');


        //l'utilisateur peut ne pas changer la totalité de ses informations, on update donc uniquement les champs renseignés

        if ($lastname != '') {
            $user->setLastname($lastname);
        }

        if ($firstname != '') {
            $user->setFirstname($firstname);
        }

        if ($email != '') {
            $user->setEmail($email);
        }

        if ($password != '') {
            $hashedPassword = $passwordHasher->hashPassword(
                $user,
                $password
            );

            $user->setPassword($hashedPassword);
        }

        if ($secteur != '') {
            $user->setSecteur($secteur);
        }

        if ($fonction != '') {
            $user->setFonction($fonction);
        }

        $em->persist($user);
        $em->flush();

        return $this->json($user,200, [], ['groups' => 'user:read']);
    }

    /**
     * @Route("/user/delete/{id}", name="delete_user", methods={"DELETE"})
     */
    public function deleteUser(int $id): Response
    {
        $em = $this->getDoctrine()->getManager();


        $user = $this->getDoctrine()->getRepository(User::class)->find($id);

        if (!$user) {
            throw $this->createNotFoundException(
                'Aucun utilisateur trouvé avec l\'identifiant' . $id
            );
        }

        $em->remove($user);
        $em->flush();

        return $this->json(['message' => 'Utilisateur supprimé'], 204, []);
    }
}
