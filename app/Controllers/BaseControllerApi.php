<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\CLIRequest;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Validation\Exceptions\ValidationException;
use Config\Services;
use Psr\Log\LoggerInterface;
use phpseclib3\Net\SFTP;
use File;
use ZipArchive;

use App\Models\Users;
use App\Models\Products;
use App\Models\Storage;


/**
 * Class BaseController
 *
 * BaseController provides a convenient place for loading components
 * and performing functions that are needed by all your controllers.
 * Extend this class in any new controllers:
 *     class Home extends BaseController
 *
 * For security be sure to declare any new methods as protected or private.
 */
class BaseControllerApi extends Controller
{
    /**
     * Instance of the main Request object.
     *
     * @var IncomingRequest|CLIRequest
     */
    protected $request;

    /**
     * An array of helpers to be loaded automatically upon
     * class instantiation. These helpers will be available
     * to all other controllers that extend BaseController.
     *
     * @var array
     */
    protected $helpers 	= ['ingram','filesystem','cookie', 'date', 'security', 'menu', 'useraccess','segmentation'];
    //protected $helpers = [];

    /**
     * Constructor.
     *
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @param LoggerInterface $logger
     */
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        /* echo json_encode($logger);
        exit; */
        // Do Not Edit This Line
        /* var_dump($request);
        exit; */
        parent::initController($request, $response, $logger);

        //--------------------------------------------------------------------
        // Preload any models, libraries, etc, here.
        //--------------------------------------------------------------------
        // E.g.: $this->session = \Config\Services::session();
        //--------------------------------------------------------------------
		// Preload any models, libraries, etc, here.
		//--------------------------------------------------------------------
		/*$this->session 		= \Config\Services::session();
		$this->segment 	  	= \Config\Services::request();
		$this->db         	= \Config\Database::connect();
		$this->validation 	= \Config\Services::validation();
		$this->encrypter 	= \Config\Services::encrypter();
		$this->userModel  	= new Users();
		$this->productsModel  	= new Products();
		$this->storageModel  	= new Storage();
		$user 				= $this->userModel->getUser(session()->get('username'));
		$segment 			= $this->request->uri->getSegment(1);
		if ($segment) {
			$subsegment 	= $this->request->uri->getSegment(2);
		} else {
			$subsegment 	= '';
		}
		$this->data			= [
			'segment' 		=> $segment,
			'subsegment' 	=> $subsegment,
			'user' 			=> $user,
			'MenuCategory' 	=> $this->userModel->getAccessMenuCategory(session()->get('role'))
		];*/
        
    }

    public function getResponse(array $responseBody, int $code = ResponseInterface::HTTP_OK)
    {
        return $this->response->setStatusCode($code)->setJSON($responseBody);
    }

    public function getRequestInput(IncomingRequest $request)
    {
        
        $input = $request->getPost();
        if (empty($input)) {
            $input = json_decode($request->getBody(), true);
        }
        return $input;
    }

    public function validateRequest($input, array $rules, array $messages = [])
    {
        
        $this->validator = Services::validation()->setRules($rules);
        if (is_string($rules)) {
            $validation = config('Validation');

            if (!isset($validation->$rules)) {
                throw ValidationException::forRuleNotFound($rules);
            }

            if (!$messages) {
                $errorName = $rules . '_errors';
                $messages = $validation->$errorName ?? [];
            }

            $rules = $validation->$rules;
        }

        return $this->validator->setRules($rules, $messages)->run($input);
    }

    public function newRoot($root){
        if (!file_exists($root)) {mkdir($root, 0777, true);}
    }

    public function SFTPImport($CONNECT_SFTP=[
                                                    "urlSFTP"=>"mercury.ingrammicro.com:22",
                                                    "user"=>"296139",
                                                    "pass"=>'4q*gk0V2$G',
                                                    "chdir"=>"FUSION/MX/N4SD76/",
                                                    "nameFile"=>"PRICE.ZIP",
                                                    "urlFile"=>"pricefile/"
                                            ])
    {
        $this->newRoot($CONNECT_SFTP['urlFile']);
        $sftp = new SFTP($CONNECT_SFTP['urlSFTP']);
        if (!$sftp->login($CONNECT_SFTP['user'], $CONNECT_SFTP['pass'])) {
            throw new Exception('Login failed');
        }        
        $files = $sftp->nlist();
        $sftp->chdir($CONNECT_SFTP['chdir']);
        $sftp->get($CONNECT_SFTP['nameFile'], $CONNECT_SFTP['urlFile']."PRICE-".date("Y-m-d").".ZIP");
        $options = array('ftp' => array('overwrite' => true));
        $stream = stream_context_create($options);
        return true;
    }

    public function fileUnzip($FILE_UNZIP=[
                                            "ROOT"=>'pricefile/',
                                            "NAME_ROOT"=>null,
                                            "FILENAME"=>'pricefile'
                                          ]){
        $this->newRoot($FILE_UNZIP['ROOT']);
        $NAME_ROOT = is_null($FILE_UNZIP['NAME_ROOT'])?'PRICE-'.date("Y-m-d").'.ZIP':$FILE_UNZIP['NAME_ROOT'];
        $zip = new ZipArchive();
        if ($zip->open($FILE_UNZIP['ROOT'].$NAME_ROOT) === TRUE) {
            $zip->extractTo($FILE_UNZIP['FILENAME']);
            $zip->close();
            return true;
        }
        else{
            return false;
        }
    }

    public function exportCSV($body=[],$objectExist=null,$id,$root="pricefile/PRICE.TXT"){   
       
        $newBody=["create"=>null,"update"=>null];       
        if($body){

            if (($gestor = fopen($root, "r")) !== FALSE) {                
                while (($data = fgetcsv($gestor, 1000, ",")) !== FALSE) {
                    $eleBody=null;
                    foreach($body as $key=>$value){
                        //if(isset($data[$id])){
                            //if(!is_null($objectExist)){
                                $operation = 'update';
                                if(!in_array(trim($data[key($id)]),$objectExist)){ 
                                    $operation = 'create';   
                                }                               
                                if(is_array($value)){
                                    foreach($value as $ele){
                                        $eleBody[$ele] = trim(utf8_encode($data[$key]));
                                    }                                
                                }else{
                                    $eleBody[$value] = trim(utf8_encode($data[$key]));
                                }
                            //}
                        //}
                    }
                    if($eleBody){
                        $newBody[$operation][] = $eleBody;
                    }
                }
            }            
        }
        return $newBody;
    }
}
