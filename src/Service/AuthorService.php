<?php

namespace App\Service;

use App\Entity\Author;

class AuthorService
{
    // fonction prend en parametre un auteur et elle increment le nombre de book de cet auteur quand elle serat appele
    public function incrementNbBooks(Author $author): void
    {
        // recuperiw le nombre actuel de livre depuis l objet Author en appelant son getter + stockage dans une variable dit currentNbBooks (nb book en ce moment)
        $currentNbBooks = $author->getNbBooks();
        // increeemente la valeur recupere de 1 et assigne le nouveau total a l objet Author en utilisant Setter 
        $author->setNbBooks($currentNbBooks + 1);
    }
}
