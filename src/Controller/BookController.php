<?php

namespace App\Controller;

use App\Entity\Author;
use App\Entity\Book;
use App\Form\BookType;
use App\Repository\AuthorRepository;
use App\Repository\BookRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class BookController extends AbstractController
{
    #[Route('/book', name: 'app_book')]
    public function index(): Response
    {
        return $this->render('book/index.html.twig', [
            'controller_name' => 'BookController',
        ]);
    }

    #[Route('/ajouter/bookkk', name: 'app_book_new')]
    public function new(Request $request, ManagerRegistry $manager_registry): Response
    {
        $book = new Book();
        $form = $this->createForm(BookType::class, $book);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $manager_registry->getManager();

            // enabled  initialisé a true 
            $book->setEnabled(true);

            // incrementer le nombre de book de l auteur donc getAuthor() pour recuperer l auteur de ce livre + $author->getNbBooks() pour recuperer le nombre de livre actuel de cet auteur + setNbBooks() pour mettre a jour le nombre de livre de cet auteur
            $author = $book->getAuthor();
            $currentNbBooks = $author->getNbBooks();
            $author->setNbBooks($currentNbBooks + 1);

            $entityManager->persist($book);
            $entityManager->flush();


            return $this->redirectToRoute('app_book_liste_from_db');
        }

        return $this->render('book/ajout_book.html.twig', [
            'formulaire_ajout_book' => $form->createView(),
        ]);
    }


    #[Route('/liste_des_bool', name: 'app_book_liste_from_db')]
    public function listeBook_From_Db(BookRepository $book_repository): Response
    {
        // Livres publies seulement c-a veut dire enabled = true et non pas tous les livre
        $publishedBooks = $book_repository->findBy(['enabled' => true]);

        // Statistiques => nous voulons compter le nombre de livres publies (enabled==true)  et non publies (enbabled==false)
        // pour cela on utilise la fonction count() de PHP qui compte le nombre d'elements dans un tableau 
        // compter le nombre de livres publies count et prend en parametres tableau des livres publies qui est $publishedBooks
        $totalPublished = count($publishedBooks);
        // compter le nombre de livres non publies count et prend en parametres tableau des livres non publies qui est le resultat de la recherche dans le repository des livres avec enabled = false qui est le tableau $tableau_des_livres_non_publies
        $tableau_des_livres_non_publies = $book_repository->findBy(['enabled' => false]);
        $totalUnpublished = count($tableau_des_livres_non_publies);

        return $this->render('book/liste_book.html.twig', [
            'liste_des_book_publie' => $publishedBooks,
            'nb_des_livres_pulies' => $totalPublished,
            'nb_des_livre_non_publie' => $totalUnpublished,
        ]);
    }


    #[Route('/modifier/book/{id}', name: 'app_book_edit')]
    public function edit(Request $request, ManagerRegistry $manager_registry, BookRepository $book_repository, $id): Response
    {
        // recupere book a modifier
        $em = $manager_registry->getManager();
        $book = $book_repository->find($id);

        // verifcation de l existence du book
        if (!$book) {
            throw $this->createNotFoundException('book n esixiste pas dans la bd');
        }

        // recuperation de l'ancien auteur (pour la gestion du nb_books si vous changez l'auteur du livre vous devez decrementer le nb_books de l'ancien auteur et incrementer le nb_books du nouvel auteur)
        $ancienAuteur = $book->getAuthor();

        // crreer le formulaire de modification
        $form = $this->createForm(BookType::class, $book);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $nouvelAuteur = $book->getAuthor();

            // Si  auteur a change donc ==> mettre à jour les compteurs =>  vous devez decrementer le nb_books de l'ancien auteur et incrementer le nb_books du nouvel auteur
            if ($ancienAuteur !== $nouvelAuteur) {
                // decrementer l ancien auteur
                $ancienAuteur->setNbBooks($ancienAuteur->getNbBooks() - 1);

                // incrementer le nouvel auteur
                $nouvelAuteur->setNbBooks($nouvelAuteur->getNbBooks() + 1);
            }

            // save des modifications + exucution de la requete sql qui met a jour la base de donnees
            $em->flush();


            return $this->redirectToRoute('app_book_liste_from_db');
        }

        return $this->render('book/edit_book.html.twig', [
            'formulaire_edit_book' => $form->createView(),
            'book' => $book,
        ]);
    }




    #[Route('/book/delete/{id}', name: 'app_book_delete')]
    public function delete(ManagerRegistry $manager_registry, BookRepository $book_repository, $id): Response
    {
        $entityManager = $manager_registry->getManager();
        $book = $book_repository->find($id);

        if (!$book) {
            throw $this->createNotFoundException('book non dispo');
        }

        // decrement le nombre de livres de l auteur
        $author = $book->getAuthor();
        $currentNbBooks = $author->getNbBooks();
        $author->setNbBooks($currentNbBooks - 1);

        // Supprimer le livre
        $entityManager->remove($book);
        $entityManager->flush();


        return $this->redirectToRoute('app_book_liste_from_db');
    }
    #[Route('/supprimer/auteur_where_nb_zero', name: 'app_book_auteur_delete')]
    public function deleteauteur_where_nb_zero(ManagerRegistry $manager_registry): void
    {
        $entityManager = $manager_registry->getManager();
        $authorRepository = $manager_registry->getRepository(Author::class);

        // chercher et trouver les auteurs avec 0 livre
        $authorsWithZeroBooks = $authorRepository->findBy(['nb_books' => 0]);

        for ($i = 0; $i < count($authorsWithZeroBooks); $i++) {
            $entityManager->remove($authorsWithZeroBooks[$i]);
        }
        if (count($authorsWithZeroBooks) > 0) {
            $entityManager->flush();
        }
    }



    #[Route('/book/show/{id}', name: 'app_book_show')]
    public function show(BookRepository $book_repository, $id): Response
    {
        $book = $book_repository->find($id);

        if (!$book) {
            throw $this->createNotFoundException('pas de livre avec cet id');
        }

        return $this->render('book/show_book.html.twig', [
            'book_a_affiche' => $book,
        ]);
    }
}
