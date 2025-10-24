<?php

namespace App\Controller;

use App\Entity\Author;
use App\Form\AuthorType;
use App\Repository\AuthorRepository;
use App\Repository\BookRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class AuthorController extends AbstractController
{
    #[Route('/author', name: 'app_author')]
    public function index(): Response
    {
        return $this->render('author/index.html.twig', [
            'controller_name' => 'AuthorController',
        ]);
    }


    // Partie 1 : La gestion des auteurs(CRUD Author)



    #[Route('/liste_author_from_db', name: 'app_liste_author_from_db')]

    //// pour tester cette fonction n'oublier pas d'abord de faire insert dans la base de donnees de deux ou 3 auteurs manuellement car nous n'avons pas encore creer le formulaire pour ajouter des auteurs
    public function RecupererListeAuthorFromDb(AuthorRepository $author_repository): Response
    {
        // Récupere tous les auteur depuis la base de donnees (nesta3mlou repository)
        $authors = $author_repository->findAll();

        // Affiche la vue avec la liste  auteurs / passer la liste a la vue avec name liste_auteurs
        return $this->render('author/Liste_auteurs_db.html.twig', [
            'liste_auteurs' => $authors,
        ]);
    }
    #[Route('/liste_author_from_db', name: 'app_liste_author_from_db')]
    public function RecupererListeAuthorFromDbb(AuthorRepository $author_repository): Response
    {
        $authors = $author_repository->findAll();
        return $this->render('author/Liste_auteurs_db.html.twig', [
            'liste_auteurs' => $authors,
        ]);
    }

    #[Route('/author/add-static', name: 'app_author_add_static')]
    public function addStatic(ManagerRegistry $manager_registry): Response
    {
        // recuperer l EntityManager pour gerer les entites /demnder l'acces minha 
        $em = $manager_registry->getManager();

        // Cree un nouvel auteur => instance de la classe author - structure de l'entite a ajouter
        $author = new Author();
        // remplir les attribut de l entite - a chaque execution on ajoute le meme auteur (abdessalem ... ) - il  faux utliser formumlaire comme nous avons fait en classe
        $author->setUsername('abdessalem');
        $author->setEmail('abdessalem@gmail.com');

        // Preparation de l'insertion - preparer data a etre inserer dans la base de donnees + informer doctrine qu'on veut ajouter ceci
        $em->persist($author);

        // execution de l'insertion dans la base de donnees + envoie data et donner l'ordre a doctrine d'executer la requete donc on utilise flush()
        $em->flush();
        // redirection vers la liste des auteurs apres l'ajout donc on utlise la fct redirectToRoute fournie par la classe abstraite AbstractController (dont herite notre controller comme j'ai expliqué en classe)

        return $this->redirectToRoute('app_liste_author_from_db');
    }




    #[Route('/author/delete/{id}', name: 'app_author_delete')]
    public function delete(ManagerRegistry $manager_registry, AuthorRepository $author_repository, $id): Response
    {
        // recuperer l EntityManager pour gerer les entites /demnder l'acces minha 
        $em = $manager_registry->getManager();
        // on peux faire injection de repository aussi pour chercher l'auteur a supprimer ou bien recuperer le repository depuis l'entity manager
        //$author = $em->getRepository(Author::class)->find($id);
        $author = $author_repository->find($id);
        // si auteur n'existe pas dil base de dionnees on affiche une erreur 404 (not found) pour cela on utilise la fct createNotFoundException fournie par la classe abstraite AbstractController (dont herite notre controller comme j'ai expliqué en classe)
        if (!$author) {
            throw $this->createNotFoundException('auteur non trouve');
        }

        // specifier  l'auteur a supprimer + informer doctrine qu'on veut supprimer ceci
        $em->remove($author);
        // excuter la suppression dans la base de donnees + envoie data et donner l'ordre a doctrine d'executer la requete donc on utilise flush()
        $em->flush();

        // Message de confirmation -optionnel - pour cela on utilise la fct addFlash fournie par la classe abstraite AbstractController (dont herite notre controller comme j'ai expliqué en classe)
        $this->addFlash('success', 'Auteur supprimé avec succès');

        return $this->redirectToRoute('app_liste_author_from_db');
    }









    /// ajout d auteur avec formulaire - recuperer data a inserer depuis le formulaire - c'est le user qui fait saisie de ses données 
    // commencer par composer require symfony/form pour faire installation du dependance necessaire a la cration des formulaires
    /// il faut faire la creation du formlulaire avant de creer cette fct dans le controller donc utliser la commande symfony console make:form AuthorType - symfony va demander le nom de lentity il faux choisir Author(comme elle est nommée dans votre projet svp ) - symfony va creer une classe AuthorType dans le dossier Form



    #[Route('/author/new', name: 'app_author_new')]
    public function new(Request $request, ManagerRegistry $em): Response
    {
        // faire une instance du auteur auteur vide
        $author = new Author();

        // creer le formulaire basé sur AuthorType (formualire crrer par la commande symfony console make:form )
        $form = $this->createForm(AuthorType::class, $author);

        // Traite la soumission du formulaire - recupere les donnees saisies par l utilisateur et les lie a l objet $author
        $form->handleRequest($request);

        // verifier si le formulaire est soumis ou non et valide ou non  donc on tuliser les fonctions isSubmitted() et isValid() fournies par symfony
        if ($form->isSubmitted() && $form->isValid()) {
            $acces_manger = $em->getManager();
            $acces_manger->persist($author);
            $acces_manger->flush();

            // redirige vers la liste des auteurs si l ajout est fait avec succes et data inserer dans la base de donnees
            return $this->redirectToRoute('app_liste_author_from_db');
        }

        // Affiche le formulaire avec mthode 1 createView() fournie par symfony
        // return $this->render('author/ajouter_auteur.html.twig', [
        //     'form' => $form->createView(),
        // ]);
        // Affiche le formulaire avec mthode 2 renderForm() fournie par symfony
        return $this->renderForm('author/ajouter_auteur.html.twig', ['formulaire_ajout_auteur' => $form]);
    }



    #[Route('/author/edit/{id}', name: 'app_author_edit')]
    public function edit(Request $request, ManagerRegistry $manager_registry, AuthorRepository $author_repository, $id): Response
    {
        // recuperer lauteur a modifier
        $entityManager = $manager_registry->getManager();
        $author = $author_repository->find($id);

        // veriofication de l'existance auteur 
        if (!$author) {
            throw $this->createNotFoundException('auteur non trouve');
        }

        // crrer le formulaire de modification
        $form = $this->createForm(AuthorType::class, $author);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // enregstrer data et executer requete  
            $entityManager->flush();

            return $this->redirectToRoute('app_liste_author_from_db');
        }

        return $this->render('author/edit.html.twig', [
            'form' => $form->createView(),
            'author' => $author,
        ]);
    }


    // methode query builder
    #[Route('/authors/by-email', name: 'authors_by_email')]
    public function authorsByEmail(AuthorRepository $authorRepository): Response
    {
        $authors = $authorRepository->listAuthorByEmail();

        return $this->render('author/list_by_email.html.twig', [
            'authors' => $authors,
        ]);
    }
}
