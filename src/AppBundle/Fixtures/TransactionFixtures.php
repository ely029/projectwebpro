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
class TransactionFixtures extends Fixture {

    public function load(ObjectManager $manager)
    {
        $em = $manager;





        $transaction = new \AppBundle\Entity\Transactions();
        $transaction->setTransactionType('CASH');
        $transaction->setUserId(706);
        $transaction->setStatus('NOT_REVIEWED');
        $transaction->setUrlImage('https://expressexpense.com/images/sample-gas-receipt.jpg');
        $transaction->setTotalAmount('200.00');
        $transaction->setCreatedDate(date('Y-m-d'));
        $transaction->setIsDeleted(0);

        $em->persist($transaction);
        $em->flush();

        $transaction = new \AppBundle\Entity\Transactions();
        $transaction->setTransactionType('CHECK REQUEST');
        $transaction->setStatus('NOT_REVIEWED');
        $transaction->setUserId(706);
        $transaction->setUrlImage('https://expressexpense.com/images/sample-gas-receipt.jpg');
        $transaction->setTotalAmount('300.00');
        $transaction->setCreatedDate(date('Y-m-d'));
        $transaction->setIsDeleted(0);
        
        $em->persist($transaction);
        $em->flush();

        $transaction = new \AppBundle\Entity\Transactions();
        $transaction->setTransactionType('REIMBURSEMENTS');
        $transaction->setStatus('NOT_REVIEWED');
        $transaction->setUrlImage('https://expressexpense.com/images/sample-gas-receipt.jpg');
        $transaction->setTotalAmount('500.00');
        $transaction->setCreatedDate(date('Y-m-d'));
        $transaction->setUserId(706);
        $transaction->setIsDeleted(0);
        
        $em->persist($transaction);
        $em->flush();

        $transaction = new \AppBundle\Entity\Transactions();
        $transaction->setTransactionType('INVOICE');
        $transaction->setStatus('NOT_REVIEWED');
        $transaction->setUrlImage('https://expressexpense.com/images/sample-gas-receipt.jpg');
        $transaction->setTotalAmount('900.00');
        $transaction->setCreatedDate(date('Y-m-d'));
        $transaction->setUserId(509);
        $transaction->setIsDeleted(0);

        $em->persist($transaction);
        $em->flush();

    }

}
