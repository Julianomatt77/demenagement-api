<?php

namespace App\Controller;

use App\Entity\Administratif;
use App\Repository\AdministratifRepository;
use App\Repository\ElementRepository;
use App\Service\AnnuaireService;
use App\Service\DataService;
use App\Service\TransformService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api/administratif')]
final class AdministratifController extends AbstractController
{
    private AnnuaireService $annuaire;
    private SerializerInterface $serializer;
    private EntityManagerInterface $em;
    private DataService $dataService;
    private TransformService $transformService;
    private AdministratifRepository $administratifRepository;

    /**
     * @param AnnuaireService $annuaire
     * @param SerializerInterface $serializer
     * @param EntityManagerInterface $em
     * @param TransformService $transformService
     * @param DataService $dataService
     * @param AdministratifRepository $administratifRepository
     */
    public function __construct(AnnuaireService $annuaire, SerializerInterface $serializer, EntityManagerInterface $em, DataService $dataService,TransformService $transformService, AdministratifRepository $administratifRepository)
    {
        $this->annuaire = $annuaire;
        $this->serializer = $serializer;
        $this->em = $em;
        $this->dataService = $dataService;
        $this->transformService = $transformService;
        $this->administratifRepository = $administratifRepository;
    }

    #[Route(name: 'get_all_administratif', defaults: ['_api_resource_class' => Administratif::class],methods: ['GET'])]
    public function index(Request $request): Response
    {
        $user = $this->annuaire->getUser($request);
        $objects = $this->administratifRepository->findBy(['user' => $user, 'deleted_at' => null], ['id' => 'asc']);

        $json = $this->serializer->serialize($objects, 'json', ['groups' => 'administratif:read']);

        return new JsonResponse($json, Response::HTTP_OK, [], true);
    }

    #[Route(name: 'add_administratif', defaults: ['_api_resource_class' => Administratif::class],methods: ['POST'])]
    public function new(Request $request): Response
    {
        $user = $this->annuaire->getUser($request);
        $content = json_decode($request->getContent(), true);

        $object = $this->serializer->deserialize(json_encode($content), Administratif::class, 'json', ['groups' => 'administratif:write']);
        $object->setUser($user);

        $this->em->persist($object);
        $this->em->flush();

        return new JsonResponse($this->serializer->serialize($object, 'json', ['groups' => 'administratif:read']), Response::HTTP_CREATED, [], true);
    }

    #[Route(path:'/{id}', name: 'get_administratif', defaults: ['_api_resource_class' => Administratif::class], methods: ['GET'])]
    public function show(Request $request, Administratif $object): Response
    {
        $user = $this->annuaire->getUser($request);
        $object = $this->administratifRepository->findOneBy(['id' => $object->getId(), 'user'=>$user, 'deleted_at' => null]);

        if (!$object) {
            return new JsonResponse(['error' => 'Element introuvable'], Response::HTTP_NOT_FOUND);
        }

        if ($object->getUser() !== $user) {
            return new JsonResponse(['error' => 'Utilisateur non autorisé'], Response::HTTP_UNAUTHORIZED);
        }

        $json = $this->serializer->serialize($object, 'json', ['groups' => 'administratif:read']);

        return new JsonResponse($json, Response::HTTP_OK, [], true);
    }

    #[Route(path: '/{id}', name: 'edit_administratif', defaults: ['_api_resource_class' => Administratif::class], methods: ['PATCH'])]
    public function edit(Request $request, Administratif $object): Response
    {
        $user = $this->annuaire->getUser($request);
        $content = json_decode($request->getContent(), true);

        $object = $this->administratifRepository->findOneBy(['id' => $object->getId(), 'user' => $user, 'deleted_at' => null]);

        if (!$object) {
            return new JsonResponse(['error' => 'Element introuvable'], Response::HTTP_NOT_FOUND);
        }

        if ($object->getUser() !== $user) {
            return new JsonResponse(['error' => 'Utilisateur non autorisé'], Response::HTTP_UNAUTHORIZED);
        }

        if ($content) {
            $object = $this->serializer->deserialize(json_encode($content), Administratif::class, 'json', ['groups' => 'administratif:write', 'object_to_populate' => $object]);
        }

        $this->em->persist($object);
        $this->em->flush();

        return new JsonResponse($this->serializer->serialize($object, 'json', ['groups' => 'administratif:read']), Response::HTTP_OK, [], true);
    }

    #[Route(path: '/{id}', name: 'delete_administratif', defaults: ['_api_resource_class' => Administratif::class],methods: ['DELETE'])]
    public function delete(Request $request, Administratif $object): Response
    {
        $user = $this->annuaire->getUser($request);
        $object = $this->administratifRepository->findOneBy(['id' => $object->getId(), 'user' => $user]);

        if (!$object) {
            return new JsonResponse(['error' => 'Element introuvable'], Response::HTTP_NOT_FOUND);
        }

        if ($object->getUser() !== $user) {
            return new JsonResponse(['error' => 'Utilisateur non autorisé'], Response::HTTP_UNAUTHORIZED);
        }

        $object->setDeletedAt(new \DateTimeImmutable());

        $this->em->persist($object);
        $this->em->flush();

        return new JsonResponse('Element supprimée !', Response::HTTP_ACCEPTED);
    }
}
