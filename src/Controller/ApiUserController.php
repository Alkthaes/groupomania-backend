<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\NormalizableInterface;
use Symfony\Component\Serializer\SerializerInterface;

class ApiUserController extends AbstractController
{
    /**
     * @Route("/api/user", name="api_user_index", methods={"GET"})
     */
    public function index(UserRepository $userRepository): Response
    {
        $users = $userRepository->findAll();

        return $this->json($users, 200, []);
    }

    /**
     * @Route("/api/user", name="api_user_create", methods={"POST"})
     */
    public function create(Request $request, SerializerInterface $serializer): Response
    {
        $receivedJson = $request->getContent();

        //dd($receivedJson);

        $user = $serializer->deserialize($receivedJson, User::class, 'json');

        dd($user);
    }
}
