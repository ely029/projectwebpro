<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Transactions
 *
 * @ORM\Table(name="transactions")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\TransactionsRepository")
 */
class Transactions
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="transaction_type", type="string", length=255)
     */
    private $transactionType;

    /**
     * @var string
     *
     * @ORM\Column(name="status", type="string", length=255)
     */
    private $status;

    /**
     * @var string
     *
     * @ORM\Column(name="url_image", type="string", length=255)
     */
    private $urlImage;

    /**
     * @var string
     *
     * @ORM\Column(name="total_amount", type="string", length=255)
     */
    private $totalAmount;

    /**
     * @var \Date
     *
     * @ORM\Column(name="created_date", type="string")
     */
    private $createdDate;

    /**
     * @var integer
     *
     * @ORM\Column(name="user_id", type="integer")
     */
    private $userId;

    /**
     * @var integer
     *
     * @ORM\Column(name="is_deleted", type="integer")
     */
    private $isDeleted;


    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set transactionType
     *
     * @param string $transactionType
     *
     * @return Transactions
     */
    public function setTransactionType($transactionType)
    {
        $this->transactionType = $transactionType;

        return $this;
    }

    /**
     * Get transactionType
     *
     * @return string
     */
    public function getTransactionType()
    {
        return $this->transactionType;
    }

    /**
     * Set status
     *
     * @param string $status
     *
     * @return Transactions
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set urlImage
     *
     * @param string $urlImage
     *
     * @return Transactions
     */
    public function setUrlImage($urlImage)
    {
        $this->urlImage = $urlImage;

        return $this;
    }

    /**
     * Get urlImage
     *
     * @return string
     */
    public function getUrlImage()
    {
        return $this->urlImage;
    }

    /**
     * Set totalAmount
     *
     * @param string $totalAmount
     *
     * @return Transactions
     */
    public function setTotalAmount($totalAmount)
    {
        $this->totalAmount = $totalAmount;

        return $this;
    }

    /**
     * Get totalAmount
     *
     * @return string
     */
    public function getTotalAmount()
    {
        return $this->totalAmount;
    }

    /**
     * Set createdDate
     *
     * @param \String $createdDate
     *
     * @return Transactions
     */
    public function setCreatedDate($createdDate)
    {
        $this->createdDate = $createdDate;

        return $this;
    }

    /**
     * Get createdDate
     *
     * @return \String
     */
    public function getCreatedDate()
    {
        return $this->createdDate;
    }

    /**
     * Set userId
     *
     * @param \integer $userId
     *
     * @return Transactions
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;

        return $this;
    }

    /**
     * Get userId
     *
     * @return \integer
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * Set isDeleted
     *
     * @param \integer $isDeleted
     *
     * @return Transactions
     */
    public function setIsDeleted($isDeleted)
    {
        $this->isDeleted = $isDeleted;

        return $this;
    }
}
