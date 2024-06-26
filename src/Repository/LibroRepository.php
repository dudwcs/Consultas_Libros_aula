<?php

namespace App\Repository;

use App\Entity\Libro;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Libro>
 *
 * @method Libro|null find($id, $lockMode = null, $lockVersion = null)
 * @method Libro|null findOneBy(array $criteria, array $orderBy = null)
 * @method Libro[]    findAll()
 * @method Libro[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LibroRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Libro::class);
    }

    public function findMaxUnidades(){
        $em = $this->getEntityManager();
        $query = $em->createQuery("SELECT max(li.unidadesVendidas) FROM App\Entity\Libro li");
        return $query->getSingleScalarResult();
    }

    public function findMaxUnidadesQB(){
        return $this->createQueryBuilder('li')
            ->select("MAX(li.unidadesVendidas) as maxUnidades")
            ->getQuery()
            ->getSingleScalarResult();
    }


    public function findLibrosSuperVentasConAutores():Libro{
        //Leer https://www.doctrine-project.org/projects/doctrine-orm/en/3.1/reference/dql-doctrine-query-language.html#joins
        //Devuelve un objeto Libro con los autores anidados en la propiedad autores
        $em = $this->getEntityManager();
        $query = $em->createQuery("SELECT a, li FROM App\Entity\Libro li join li.autores a where li.unidadesVendidas= (select max(li2.unidadesVendidas) FROM App\Entity\Libro li2)");
        return $query->getOneOrNullResult();

    }
    public function findLibrosSuperVentasConAutoresQB():?Libro{


        $subqueryMaxUnidades = $this->createQueryBuilder('li2')
            ->select("MAX(li2.unidadesVendidas) as maxUnidades")
            ->getQuery();
            //->getSingleScalarResult();

        $qb=  $this->createQueryBuilder('li')
        ->addSelect('a') //para que traiga también los autores en una única consulta
        ->innerJoin('li.autores', 'a'); 


        $qb = $qb
       ->andWhere($qb->expr()->eq("li.unidadesVendidas", "(".$subqueryMaxUnidades->getDQL().")"))
             //  ->andWhere('li.unidadesVendidas = "(".$subqueryMaxUnidades->getDQL().")"')
             //  ->setParameter('val', "(".$subqueryMaxUnidades->getDQL().")")
              // ->setParameter('val', $subqueryMaxUnidades->getSingleScalarResult())
               ->getQuery()            
           ;

        return $qb->getOneOrNullResult();
    }


//
    // public function findLibrosSuperVentasConAutores2():Libro{
    //     //Leer https://www.doctrine-project.org/projects/doctrine-orm/en/3.1/reference/dql-doctrine-query-language.html#joins
    //     //Devuelve un objeto Libro sin autores
    //     $em = $this->getEntityManager();
    //     $query = $em->createQuery("SELECT li FROM App\Entity\Libro li where li.unidadesVendidas= (select max(li2.unidadesVendidas) FROM App\Entity\Libro li2)");
    //     return $query->getOneOrNullResult();

    // }
    //    /**
    //     * @return Libro[] Returns an array of Libro objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('l')
    //            ->andWhere('l.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('l.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Libro
    //    {
    //        return $this->createQueryBuilder('l')
    //            ->andWhere('l.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
