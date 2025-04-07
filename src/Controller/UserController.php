<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\AnnuaireService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

class UserController extends AbstractController
{
    private EntityManagerInterface $em;
    private UserRepository $userRepository;
    private AnnuaireService $annuaire;
    private UserPasswordHasherInterface $userPasswordHasher;

    public function __construct(EntityManagerInterface $manager, UserRepository $userRepository, AnnuaireService $annuaire, UserPasswordHasherInterface $userPasswordHasher)
    {
        $this->em   = $manager;
        $this->userRepository = $userRepository;
        $this->annuaire = $annuaire;
        $this->userPasswordHasher = $userPasswordHasher;
    }

    //Création d’un utilisateur
    #[\Symfony\Component\Routing\Annotation\Route(
        path: '/api/register', name: 'api_register', defaults: ['_api_resource_class' => User::class,], methods: ['POST']
    )]
    public function register(Request $request, SerializerInterface $serializer): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $isRequestValid = $this->annuaire->validateRegistrationInfos($data);
        if (!$isRequestValid) {
            return new JsonResponse([
                "status"  => false,
                "message" => "Un nom d'utilisateur, un mot de passe et une adresse email valide sont nécessaires pour créer un compte !"
            ], Response::HTTP_FORBIDDEN);
        }

        $checkEmail = $this->userRepository->findOneBy(['email' => $data["email"]]);
        if ($checkEmail) {
            if ($checkEmail->getDeletedAt()) {
                return new JsonResponse([
                    "status"  => false,
                    "message" => "Ce compte a été supprimé, vous devez en créer un nouveau ou nous contacter!"
                ], Response::HTTP_FORBIDDEN);
            }

            return new JsonResponse([
                "status"  => false,
                "message" => "Cet email existe déjà, vous devez en choisir un autre !"
            ], Response::HTTP_FORBIDDEN);
        }

        $checkUsername = $this->userRepository->findOneBy(['username' => $data["username"]]);
        if ($checkUsername) {
            if ($checkUsername->getDeletedAt()) {
                return new JsonResponse([
                    "status"  => false,
                    "message" => "Ce compte a été supprimé, vous devez en créer un nouveau ou nous contacter!"
                ], Response::HTTP_FORBIDDEN);
            }

            return new JsonResponse([
                "status"  => false,
                "message" => "Ce nom d'utilisateur existe déjà, vous devez en choisir un autre !"
            ], Response::HTTP_FORBIDDEN);
        }

        $user = $serializer->deserialize(json_encode($data), User::class, 'json', ['groups' => 'user:write']);

        $user->setRegisteredAt(new \DateTimeImmutable());
        $user->setPassword($this->userPasswordHasher->hashPassword($user, $user->getPassword()));
        $user->setRoles(["ROLE_USER"]);

        $this->em->persist($user);
        $this->em->flush();

        return new JsonResponse([
            "status"  => true,
            "message" => "L’utilisateur a été créé avec succès !"
        ], Response::HTTP_CREATED);
    }

    #[Route(
        path: '/api/users-infos', name: 'app_user_show', defaults: ['_api_resource_class' => User::class,], methods: ['GET'],
    )]
    public function show( Request $request, SerializerInterface $serializer): Response
    {
        $connectedUser = $this->annuaire->getUser($request);
        $json = $serializer->serialize($connectedUser, 'json', ['groups' => 'user:read']);

        return new JsonResponse($json, Response::HTTP_OK, [], true);
    }

    #[Route(
        path: '/api/user-delete', name: 'app_user_delete', defaults: ['_api_resource_class' => User::class,], methods: ['DELETE'],
    )]
    public function delete( Request $request): Response
    {
        $connectedUser = $this->annuaire->getUser($request);
        $connectedUser->setDeletedAt(new \DateTimeImmutable());
        $this->em->persist($connectedUser);
        $this->em->flush();

        return new JsonResponse([
            "status"  => true,
            "message" => "Votre compte a été supprimé avec succès !"
        ], Response::HTTP_CREATED);
    }

}
