<?php

namespace App\Controller;

use App\Entity\Demenageur;
use App\Form\DemenageurType;
use App\Repository\DemenageurRepository;
use App\Repository\UserRepository;
use App\Service\AnnuaireService;
use App\Service\DataService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api/demenageurs')]
final class DemenageurController extends AbstractController
{
    private AnnuaireService $annuaire;
    private DataService $dataService;
    private SerializerInterface $serializer;
    private EntityManagerInterface $em;
    private DemenageurRepository $demenageurRepository;

    public function __construct(AnnuaireService $annuaire, DataService $dataService, SerializerInterface $serializer, EntityManagerInterface $em, DemenageurRepository $demenageurRepository)
    {
        $this->annuaire = $annuaire;
        $this->dataService = $dataService;
        $this->serializer = $serializer;
        $this->em = $em;
        $this->demenageurRepository = $demenageurRepository;
    }


    #[Route(name: 'get_all_demenageurs', defaults: ['_api_resource_class' => Demenageur::class],methods: ['GET'])]
    public function index(Request $request): Response
    {
        $user = $this->annuaire->getUser($request);
        $demenageurs = $this->demenageurRepository->findBy(['user' => $user, 'deleted_at' => null], ['id' => 'desc']);

        $json = $this->serializer->serialize($demenageurs, 'json', ['groups' => 'demenageur:read']);

        return new JsonResponse($json, Response::HTTP_OK, [], true);
    }

    #[Route(name: 'add_demenageur', defaults: ['_api_resource_class' => Demenageur::class],methods: ['POST'])]
    public function new(Request $request): Response
    {
        $user = $this->annuaire->getUser($request);

        $demenageur = $this->serializer->deserialize($request->getContent(), Demenageur::class, 'json', ['groups' => 'demenageur:write']);
        $demenageur->setUser($user);
        $demenageur = $this->dataService->setDemenageurPriceLeft($demenageur);

        $this->em->persist($demenageur);
        $this->em->flush();

        return new JsonResponse($this->serializer->serialize($demenageur, 'json', ['groups' => 'demenageur:read']), Response::HTTP_CREATED, [], true);
    }

    #[Route(path:'/{id}', name: 'get_demenageur', defaults: ['_api_resource_class' => Demenageur::class], methods: ['GET'])]
    public function show(Request $request, Demenageur $demenageur): Response
    {
        $user = $this->annuaire->getUser($request);
        $demenageur = $this->demenageurRepository->findOneBy(['id' => $demenageur->getId(), 'user'=>$user, 'deleted_at' => null]);

        if (!$demenageur) {
            return new JsonResponse(['error' => 'Déménageur introuvable'], Response::HTTP_NOT_FOUND);
        }

        if ($demenageur->getUser() !== $user) {
            return new JsonResponse(['error' => 'Utilisateur non autorisé'], Response::HTTP_UNAUTHORIZED);
        }

        $json = $this->serializer->serialize($demenageur, 'json', ['groups' => 'demenageur:read']);

        return new JsonResponse($json, Response::HTTP_OK, [], true);
    }

    #[Route(path: '/{id}', name: 'edit_demenageur', defaults: ['_api_resource_class' => Demenageur::class], methods: ['PATCH'])]
    public function edit(Request $request, Demenageur $demenageur): Response
    {
        $user = $this->annuaire->getUser($request);
        $content = json_decode($request->getContent(), true);
        $demenageur = $this->demenageurRepository->findOneBy(['id' => $demenageur->getId(), 'user' => $user, 'deleted_at' => null]);

        if (!$demenageur) {
            return new JsonResponse(['error' => 'Déménageur introuvable'], Response::HTTP_NOT_FOUND);
        }

        if ($demenageur->getUser() !== $user) {
            return new JsonResponse(['error' => 'Utilisateur non autorisé'], Response::HTTP_UNAUTHORIZED);
        }

        if ($content) {
            $demenageur = $this->serializer->deserialize(json_encode($content), Demenageur::class, 'json', ['groups' => 'demenageur:write', 'object_to_populate' => $demenageur]);
        }

        $demenageur = $this->dataService->setDemenageurPriceLeft($demenageur);

        $this->em->persist($demenageur);
        $this->em->flush();

        return new JsonResponse($this->serializer->serialize($demenageur, 'json', ['groups' => 'demenageur:read']), Response::HTTP_OK, [], true);
    }

    #[Route(path: '/{id}', name: 'delete_demenageur', defaults: ['_api_resource_class' => Demenageur::class],methods: ['DELETE'])]
    public function delete(Request $request, Demenageur $demenageur): Response
    {
        $user = $this->annuaire->getUser($request);
        $demenageur = $this->demenageurRepository->findOneBy(['id' => $demenageur->getId(), 'user' => $user]);

        if (!$demenageur) {
            return new JsonResponse(['error' => 'Déménageur introuvable'], Response::HTTP_NOT_FOUND);
        }

        if ($demenageur->getUser() !== $user) {
            return new JsonResponse(['error' => 'Utilisateur non autorisé'], Response::HTTP_UNAUTHORIZED);
        }

        $demenageur->setDeletedAt(new \DateTimeImmutable());

        $this->em->persist($demenageur);
        $this->em->flush();

        return new JsonResponse('Déménageur supprimée !', Response::HTTP_ACCEPTED, [], true);
    }
}
