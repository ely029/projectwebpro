<?php

namespace AppBundle\Controller\Web;

use AppBundle\Entity\Employee;
use AppBundle\Entity\User;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Security\Core\Security;
use PHPExcel;
use PHPExcel_IOFactory;
use PHPExcel_Shared_ZipArchive;

class SuperAdminController extends Controller {

  /**
    * @Route("/owner/login", name="superAdminLogin")
    */
   public function superAdminLoginAction(Request $request)
   {
     /** @var $session Session */
     $session = $request->getSession();

     $authErrorKey = Security::AUTHENTICATION_ERROR;
     $lastUsernameKey = Security::LAST_USERNAME;

     // get the error if any (works with forward and redirect -- see below)
     if ($request->attributes->has($authErrorKey)) {
         $error = $request->attributes->get($authErrorKey);
     } elseif (null !== $session && $session->has($authErrorKey)) {
         $error = $session->get($authErrorKey);
         $session->remove($authErrorKey);
     } else {
         $error = null;
     }

     if (!$error instanceof AuthenticationException) {
         $error = null; // The value does not come from the security component.
     }

     // last username entered by the user
     $lastUsername = (null === $session) ? '' : $session->get($lastUsernameKey);

     $csrfToken = $this->has('security.csrf.token_manager')
         ? $this->get('security.csrf.token_manager')->getToken('authenticate')->getValue()
         : null;

       return $this->render('@FOSUser/Security/admin-login.html.twig', [
         'last_username' => $lastUsername,
         'error' => $error,
         'csrf_token' => $csrfToken,
       ]);
   }

   private function getEmployeeRecord($companyId) {
       if ($companyId) {
           $user = $this->getUser();
           $company = $this->getDoctrine()
               ->getRepository('AppBundle:Company')
               ->find($companyId);
           $employeeRecord = $this->getDoctrine()
               ->getRepository('AppBundle:Employee')
               ->findOneBy(['user' => $user, 'company' => $company]);

           return $employeeRecord;
       } else {
           return null;
       }
   }

    /**
     * @Route("/superadmin/{id}", name="showSuperAdminDashboard")
     */
    public function showSuperAdminDashboardAction($id, Request $request){

      $isQbIntegrated = $this->getDoctrine()
          ->getRepository('AppBundle:Company')
          ->find($id)
          ->isQbIntegrated();
      return $this->render(
                      'dashboard/super-admin-dashboard.html.twig', ['companyId' => $id, 'isQbIntegrated' => $isQbIntegrated,  'employeeRecord' => $this->getEmployeeRecord($id),
              'error' => $request->query->get('error')]
      );

    }

    /**
    * @Route("/superadmin/transactionlist/{id}", name="showSuperAdminTransactionList")
    */
   public function showSuperAdminTransactionListAction($id, Request $request) {
       $employeeRecord = $this->getEmployeeRecord($id);
       $employeeId = null;
       if ($employeeRecord) {
           $employeeId = $this->getEmployeeRecord($id)->getId();
       }

       $submitter = $this->getDoctrine()
               ->getRepository('AppBundle:Transactions')
               ->getSubmitter();


       return $this->render(
                       'dashboard/super-admin-transaction-list.html.twig', ['companyId' => $id,'employeeRecord' => $employeeRecord, 'employeeId' => $employeeId,
               'error' => $request->query->get('error'),
               'submitter' => $submitter

             ]
       );
   }

   /**
    * @Route("/transactions", name="getTransactions")
    */
   public function getTransactionsAction(Request $request) {

       $type = $request->request->get('type');
       $submitter = $request->request->get('submitter');
       $status = $request->request->get('status');
       $startDate = $request->request->get('startDate');
       $endDate = $request->request->get('endDate');

       $transaction = $this->getDoctrine()
          ->getRepository('AppBundle:Transactions')
          ->getTransactions($type,$submitter,$status,$startDate,$endDate);

          $totalAmount = $this->getDoctrine()
          ->getRepository('AppBundle:Transactions')
          ->getTotalAmount($type,$submitter,$status,$startDate,$endDate);

           $response = "<table class='table' id='data_default'>
             <tr style='background-color:gray;''>
               <th>Transaction Date</th>
               <th>Transaction Type</th>
               <th>Submitter</th>
               <th>Status</th>
               <th>Amount<br/>$".$totalAmount[0]['totalAmount']."</th>
             </tr>";

          foreach($transaction as $transactions){

              $response .='<tr>';
              $response .= '<td>'.$transactions['createdDate'].'</td>';
              $response .= '<td>'.$transactions['transaction_type'].'</td>';
              $response .= '<td>'.$transactions['submitter'].'</td>';          
              $response .= '<td>'.$transactions['status'].'</td>';
              $response .= '<td>'.$transactions['totalAmount'].'</td>';
              $response .='</tr>';
              
          }

            $response.='</table>';

             return new JsonResponse($response);

     }

   /**
    * @Route("/superadmin/reports/{id}", name="showSuperAdminReports")
    */
   public function showSuperAdminReportsAction($id, Request $request) {
       $employeeRecord = $this->getEmployeeRecord($id);
       $employeeId = null;
       if ($employeeRecord) {
           $employeeId = $this->getEmployeeRecord($id)->getId();
       }

       $submitter = $this->getDoctrine()
               ->getRepository('AppBundle:Transactions')
               ->getSubmitter();

       return $this->render(
                       'dashboard/super-admin-reports.html.twig', ['companyId' => $id,'employeeRecord' => $employeeRecord, 'employeeId' => $employeeId,
               'error' => $request->query->get('error'),
               'submitter' => $submitter
             ]
       );
   }

   /**
    * @Route("/superadmin/user/{id}", name="showSuperAdminUser")
    */
   public function showSuperAdminUserAction($id, Request $request) {
       $employeeRecord = $this->getEmployeeRecord($id);
       $employeeId = null;
       $em = $this->getDoctrine()->getManager();

       if ($employeeRecord) {
           $employeeId = $this->getEmployeeRecord($id)->getId();
       }

              $disabledUsers = $em
       ->getRepository('AppBundle:User')
       ->getDisabledUsers();

       $users = $em
       ->getRepository('AppBundle:User')
       ->getUsers();



       return $this->render(
                       'dashboard/super-admin-user.html.twig', ['companyId' => $id,'employeeRecord' => $employeeRecord, 'employeeId' => $employeeId,
               'error' => $request->query->get('error'),
               'disabledUsers' => $disabledUsers,
               'users' => $users

             ]
       );
   }

   /**
    *
    * @Route("/admin/new/user", name="superAdminNewUser")
    *
    */
   public function addUserAction(Request $request)
   {
       $firstName = $request->request->get('fname');
       $lastName = $request->request->get('lname');
       $email =   $request->request->get('email');
       $roles = $request->request->get('roles');
       $phonenumber = $request->request->get('phonenumber');
       $password = 'P@ssw0rd$$';
       $accountantEmail = '';
       $id = $request->request->get('id');

       $em = $this->getDoctrine()->getManager();

       $userManager = $this->get('fos_user.user_manager');
       $error = $request->query->get('error');

       $emailBody = '';

        //get last id entered
        $e = $em->getConnection()->prepare('select max(id) as last_id from fos_user order by id');
        $e->execute();
        $lastid = $e->fetch();
        $last_id = $lastid['last_id'];
        $a = $last_id + 1;


              $add = $em->getRepository('AppBundle:User')
       ->addUser($em,$id,$userManager,$firstName, $lastName, $email, $password,$accountantEmail,$a,$roles,$phonenumber);

        


       $message = (new \Swift_Message("Welcome new User"))
               ->setFrom("no-reply@projectprohub.com")
               ->setTo($email)
               ->setBody(
                       $this->renderView(
                           'email/adduser.html.twig',
                           [
                               //'costs' => $overBudgetCosts,
                               //'user' => $employeeName,
                               //'totalAmount' => $totalAmount
                           ]
                       ), 'text/html'
               );

       $this->get('mailer')->send($message);

       return new JsonResponse(array('error' => $error));
   }


   /**
    *
    * @Route("/user/disable", name="disableUser")
    *
    */
   public function disableUserAction(Request $request){

       $em = $this->getDoctrine()->getManager();
          $disable = $em
       ->getRepository('AppBundle:User')
       ->disableUser($request->request->get('id'));

      return new Response('ok');
   }


   /**
    *
    * @Route("/user/enable", name="enableUser")
    *
    */
   public function enableUserAction(Request $request){

       $em = $this->getDoctrine()->getManager();
          $disable = $em
       ->getRepository('AppBundle:User')
       ->enableUser($request->request->get('id'));

      return new Response('ok');
   }

   /**
    *
    * @Route("/process/all-transaction", name="processExcelAllTransaction")
    *
    */
   public function processExcelAllTransactionAction(Request $request){

       $em = $this->getDoctrine()->getManager();
        
        $type = $request->request->get('type');
       $submitter = $request->request->get('submitter');
       $status = $request->request->get('status');
       $startDate = $request->request->get('startDate');
       $endDate = $request->request->get('endDate');

       $transaction = $this->getDoctrine()
          ->getRepository('AppBundle:Transactions')
          ->getTransactions($type,$submitter,$status,$startDate,$endDate);

          $totalAmount = $this->getDoctrine()
          ->getRepository('AppBundle:Transactions')
          ->getTotalAmount($type,$submitter,$status,$startDate,$endDate);
     
   //  print_r($this->get('kernel')->getRootDir() . '/excelfiles/all-transaction/'); exit();
                
      $objPHPExcel = new PHPExcel();
   $objPHPExcel->getProperties()
   ->setCreator("Temporaris")
   ->setLastModifiedBy("Temporaris")
   ->setTitle("Template Relevé des heures intérimaires")
   ->setSubject("Template excel")
   ->setDescription("Template excel permettant la création d'un ou plusieurs relevés d'heures")
   ->setKeywords("Template excel");
   $objPHPExcel->setActiveSheetIndex(0);
   $objPHPExcel->getActiveSheet()->SetCellValue('A3', "Date");
   $objPHPExcel->getActiveSheet()->SetCellValue('B3', "Type");
   $objPHPExcel->getActiveSheet()->SetCellValue('C3', "submitter");
   $objPHPExcel->getActiveSheet()->SetCellValue('D3', "Status");
   $objPHPExcel->getActiveSheet()->SetCellValue('E3', "Amount");

   $writer = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');  
   header('Content-Type: application/vnd.ms-excel');
   header('Content-Disposition: attachment;filename="excel.xls"');
   header('Cache-Control: max-age=0');
   $writer->save($this->get('kernel')->getRootDir() . '/result.xlsx');

    return new Response('true');

   }

}
