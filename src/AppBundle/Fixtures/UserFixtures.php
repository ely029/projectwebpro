<?php

namespace AppBundle\Fixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of PurchaseFixtures
 *
 * @author Harold
 */
class UserFixtures extends Fixture {

    public function load(ObjectManager $manager)
    {
        $em = $manager;





        $user = new \AppBundle\Entity\User();

            $user->setUsername('superadmin@projectprohub.com');
            $user->setEmail('superadmin@projectprohub.com');
            $user->setPlainPassword('superadmin');
            $user->setRoles(array('ROLE_SUPER_ADMIN'));
            $user->setSalt(null);
            $user->setEnabled(true);
            $user->setFirstName('Krista');
            $user->setLastName('Philipps');


        $em->persist($user);
        $em->flush();

    }

}
