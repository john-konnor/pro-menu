<?php

namespace App\Repository;

use App\Entity\Menu;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Contracts\Translation\TranslatorInterface;
use Doctrine\DBAL\Connection;

/**
 * @extends ServiceEntityRepository<Menu>
 *
 * @method Menu|null find($id, $lockMode = null, $lockVersion = null)
 * @method Menu|null findOneBy(array $criteria, array $orderBy = null)
 * @method Menu[]    findAll()
 * @method Menu[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MenuRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Menu::class);
    }

    public function save(Menu $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Menu $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function getMenu(Connection $connection, TranslatorInterface $translator, int $type, array $roles): array
    {
        $parentChild = $connection->fetchAllAssociative('
            SELECT parent, p.type AS type_p, child, c.type AS type_c
            FROM t_auth_item_child
            JOIN t_auth_item p ON parent=p.name
            JOIN t_auth_item c ON child=c.name
        ');

        $resolvedPaths = [];

        foreach($parentChild as $key => $parentChildItem) {
            if($parentChildItem['type_c']==2) {
                $resolvedPaths[$parentChildItem['child']][] = $parentChildItem['parent'];
                unset($parentChild[$key]);
            }
        }

        $chainRoles = [];
        foreach($parentChild as $key => $parentChildItem) {
            $chainRoles[$parentChildItem['parent']][] = $parentChildItem['child'];
            foreach($chainRoles as $key_chain => $chainRolesItem) {
                if(array_search($parentChildItem['parent'], $chainRolesItem) !== false) {
                    $chainRoles[$key_chain] = array_merge($chainRoles[$parentChildItem['parent']], $chainRoles[$key_chain]);
                }
            }
        }

        foreach($resolvedPaths as $key => $rolesArr) {
            foreach($chainRoles as $key_roles => $chainRolesItem) {
                foreach($chainRolesItem as $key_role => $roleItem){
                    if(array_search($roleItem, $rolesArr) !== false) {
                        $resolvedPaths[$key][] = $key_roles;
                    }
                }
            }
        }

        $resolvedPathsForUser = [];
        foreach($resolvedPaths as $key => $value) {
            if(!empty(array_intersect($value, $roles))) $resolvedPathsForUser[] = $key;
        }

        $allMenu =  $this->createQueryBuilder('m')
            ->select('m.name', 'm.path')
            ->where('m.type = :type')
            ->setParameter('type', $type)
            ->orderBy('m.sort', 'ASC')
            ->getQuery()
            ->getResult()
        ;

        $outputMenu = [];
        foreach($allMenu as $key => $menuItem) {
            if(!empty(array_intersect($menuItem, $resolvedPathsForUser))) $outputMenu[] = [
                                                'name' => $translator->trans($menuItem['name']),
                                                'path' => $menuItem['path']
                                            ];
        }
        
        if(!empty(array_intersect($roles, ['ROLE_USER']))) {
            foreach($outputMenu as $key => $item){
                if($item['path'] == '/login' || $item['path'] == '/signup') unset($outputMenu[$key]);
            }
        }

        return array_values($outputMenu);
    }

//    /**
//     * @return Menu[] Returns an array of Menu objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('m')
//            ->andWhere('m.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('m.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Menu
//    {
//        return $this->createQueryBuilder('m')
//            ->andWhere('m.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
