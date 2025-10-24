<?php

namespace App\Repository;

use App\Entity\Book;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Book>
 */
class BookRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Book::class);
    }

    // fonction en utlisant dql pour faire rechercher livre par son category
    public function findBycategoryLivre($category): array
    {
        $entityManager = $this->getEntityManager();
        $requete_dql = $entityManager->createQuery(' SELECT livre FROM App\Entity\Book livre where livre.category=:mehdi ')
            ->setParameter('mehdi', $category);
        return $requete_dql->getResult();
    }
    // meme fonction deja faite avec dql mais cette fois en utlisant le QueryBuilder afin de montrer la diff entre les deux approches 
    public function findByCategoryQB(string $category): array
    {
        $qb = $this->createQueryBuilder('b')
            ->andWhere('b.category = :cat')
            ->setParameter('cat', $category)
            ->orderBy('b.publicationDate', 'DESC');

        // getQuery() transforme le QueryBuilder en Query, getResult() exécute et retourne les entités
        return $qb->getQuery()->getResult();
    }


    /// Zxemple de Jointure en utilisant DQL 
    /// jointure entre deux tables book et author en utilisant dql 
    public function countBooksByAuthor(): array
    // stp le type de la fonction est un array et non pas Response nous ne sont pas en train d'ecrire une fonction dans un controleur
    {
        $entityManager = $this->getEntityManager();
        // On selectionne le nom d'utilisateur de l'auteur (a.username) et le nombre de livres (en comptant 
        // les identifiants des livres) que chaque auteur a écrits. On donne un alias bookCount à ce compte.
        // On effectue une jointure gauche entre l'entite Author et la collection books (qui est la relation definie dans l'entite Author vers Book) On donne le alias b ljointure des livre
        // Une jointure gauche permet  d inclure tous les auteurs meme ceux qui n ont aucun livre ..>dans ce cas le compte sera 0
        $query = $entityManager->createQuery('SELECT a.username, COUNT(b.id) as bookCount 
        FROM App\Entity\Author a 
        LEFT JOIN a.books b 
        GROUP BY a.id 
        ORDER BY bookCount DESC
    ');
        // execute la requete et retourne un tableau de résultats
        return $query->getResult();
    }

    // fonction pour recuperer liste des book selon une date donnée
    public function findBooksAfterDate($date): array
    {
        // Cree une requete QueryBuilder pour l'entite book  et lui donne l'alias b'
        return $this->createQueryBuilder('b')
            // aajoute une condition WHERE : on ne conserve que les livre dont la date de publication est strictement > :date
            ->andWhere('b.publicationDate > :date')
            // Ajoute une autre condition WHERE : on ne conserve que les livres dont le champ "enabled" est true
            // (Commentaire : cette ligne est optionnelle selon votre logique metier — elle filtre les livres désactive)
            ->andWhere('b.enabled = true')
            // lie la valeur reelle au parametre nomme :date utilise dans la requete 
            ->setParameter('date', $date)
            // Indique l ordre des resultats : tri decroissant sur la date de publication 
            ->orderBy('b.publicationDate', 'DESC')
            // transforme le QueryBuilder en Query executable (dql) afin que doctrine la comprendre et la transforme en sql pour manipuler des tables dans la bd 
            ->getQuery()
            // Execute la Query et retourne les resultat sous forme de liste (tableau)
            ->getResult();
    }



    //    /**
    //     * @return Book[] Returns an array of Book objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('b')
    //            ->andWhere('b.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('b.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Book
    //    {
    //        return $this->createQueryBuilder('b')
    //            ->andWhere('b.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
