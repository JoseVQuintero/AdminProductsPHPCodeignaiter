<?php

error_reporting(E_ALL);

ini_set("display_errors", 1);

set_time_limit(0);



require_once APPPATH."/Helpers/support/web_browser.php";

require_once APPPATH."/Helpers/support/tag_filter.php";



$getUrl = [

	"getProduct"=>[

			"mx"=>"https://mx.ingrammicro.com/Site/ProductDetail?id=",

			"co"=>"https://co.ingrammicro.com/_layouts/CommerceServer/IM/ProductDetails.aspx?id=",

			"pe"=>"",

			"ca"=>"https://ca.ingrammicro.com/Site/ProductDetail?id="

	],

	"getProducts"=>[

		"mx"=>"https://mx.ingrammicro.com/Site/Search/DoSearch",

		"co"=>"",

		"pe"=>"",

		"ca"=>"https://ca.ingrammicro.com/Site/Search/DoSearch"

	],

	"filter"=>[]

];



function returnIngramUrl(){

	return [

		"getProduct"=>[

				"mx"=>"https://mx.ingrammicro.com/Site/ProductDetail?id=",

				"co"=>"https://co.ingrammicro.com/_layouts/CommerceServer/IM/ProductDetails.aspx?id=",

				"pe"=>"",

				"ca"=>"https://ca.ingrammicro.com/Site/ProductDetail?id="

		],

		"getProducts"=>[

			"mx"=>"https://mx.ingrammicro.com/Site/Search/DoSearch",

			"co"=>"",

			"pe"=>"",

			"ca"=>"https://ca.ingrammicro.com/Site/Search/DoSearch"

		],

		"filter"=>[]

	];

}



//$mysqli = new mysqli("localhost", "root", "", "bdicentral");



$mysqli = new mysqli(

	getenv('database.default.hostname'), 

	getenv('database.default.username'), 

	getenv('database.default.password'), 

	getenv('database.default.database')

);



function eliminar_acentos($cadena)

{

    //Reemplazamos la A y a

    $cadena = str_replace(

        array('Á', 'À', 'Â', 'Ä', 'á', 'à', 'ä', 'â', 'ª'),

        array('A', 'A', 'A', 'A', 'a', 'a', 'a', 'a', 'a'),

        $cadena

    );



    //Reemplazamos la E y e

    $cadena = str_replace(

        array('É', 'È', 'Ê', 'Ë', 'é', 'è', 'ë', 'ê'),

        array('E', 'E', 'E', 'E', 'e', 'e', 'e', 'e'),

        $cadena

    );



    //Reemplazamos la I y i

    $cadena = str_replace(

        array('Í', 'Ì', 'Ï', 'Î', 'í', 'ì', 'ï', 'î'),

        array('I', 'I', 'I', 'I', 'i', 'i', 'i', 'i'),

        $cadena

    );



    //Reemplazamos la O y o

    $cadena = str_replace(

        array('Ó', 'Ò', 'Ö', 'Ô', 'ó', 'ò', 'ö', 'ô'),

        array('O', 'O', 'O', 'O', 'o', 'o', 'o', 'o'),

        $cadena

    );



    //Reemplazamos la U y u

    $cadena = str_replace(

        array('Ú', 'Ù', 'Û', 'Ü', 'ú', 'ù', 'ü', 'û'),

        array('U', 'U', 'U', 'U', 'u', 'u', 'u', 'u'),

        $cadena

    );



    //Reemplazamos la N, n, C y c

    $cadena = str_replace(

        array('Ñ', 'ñ', 'Ç', 'ç'),

        array('N', 'n', 'C', 'c'),

        $cadena

    );



    return $cadena;

}



function slugify($text)

{

    $text = eliminar_acentos($text);

    // replace non letter or digits by -

    $text = preg_replace('~[^\pL\d]+~u', '-', $text);



    // transliterate

    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);



    // remove unwanted characters

    $text = preg_replace('~[^-\w]+~', '', $text);



    // trim

    $text = trim($text, '-');



    // remove duplicate -

    $text = preg_replace('~-+~', '-', $text);



    // lowercase

    $text = strtolower($text);



    if (empty($text)) {



        return htmlentities($text);

    }



    return $text;

}



function RCca($sku=null,$reset=false){

	$response=null;

	$db       = \Config\Database::connect();



	if(!$reset){

		if(!is_null($sku)){

			$sku = !is_array($sku)?[$sku]:$sku;



			$skus = $db->table('products_ca')

			->select('sku')

			->where('id_category is null or id_category = \'\' or id_manufacturer is null or id_manufacturer = \'\'')

			->where('sku in (\''.implode("','",$sku).'\')')

			->get()->getResultArray();



		}else{

			$skus = $db->table('products_ca')

			->select('sku')

			->where('id_category is null or id_category = \'\' or id_manufacturer is null or id_manufacturer = \'\'')

			->get()->getResultArray();

		}

	}else{

		if(!is_null($sku)){

			$sku = !is_array($sku)?[$sku]:$sku;

			$skus = $db->table('products_ca')

			->select('sku')

			->where('sku in (\''.implode("','",$sku).'\')')

			->get()->getResultArray();

		}else{

			$skus = $db->table('products_ca')->select('sku')->get()->getResultArray();

		}

	}

	

	

	if(!is_null($sku)){

		$sku = !is_array($sku)?[$sku]:$sku;

		$skus  = array_filter($skus, 

			function($item) use($sku){       

				return in_array($item['sku'],$sku);

			}

		);

	}

	

	////////////////////////////////////////////////////////////////////////////////////////

	//igualar categories => categorias//////////////////////////////////////

	///////////////////////////////////////////////////////////////////////////////////////

	$count=0;

	foreach($skus as $item){

		$count++;

		if(file_exists('./getContents/ca/'.$item['sku'].'.json')){



			$product = json_decode(file_get_contents('./getContents/ca/'.$item['sku'].'.json'),true);



			if(empty($product['productDetail']['category'])||is_null($product['productDetail']['category'])||

			   empty($product['productDetail']['vendor'])||is_null($product['productDetail']['vendor'])){

				$productFail=(file_exists('./getContents/ca_product_json_fail.json'))?json_decode(file_get_contents('./getContents/ca_product_json_fail.json'),true):[];

				$productFail[]=$item['sku'];

				file_put_contents('./getContents/ca_product_json_fail.json',json_encode(array_unique($productFail)));

				continue;

			}



			$category = $db->table('categorias_ca')->where(['name' => $product['productDetail']['category'], 'type' => 'category-product'])->get()->getResultArray();

			$subCategory = $db->table('categorias_ca')->where(['name' => $product['productDetail']['subCategory'], 'type' => 'sub-product'])->get()->getResultArray();

			$productLine = $db->table('categorias_ca')->where(['name' => $product['productDetail']['productLine'], 'type' => 'line-product'])->get()->getResultArray();

			$manufacturer = $db->table('manufacturer_ca')->where(['manufacturer' => $product['productDetail']['vendor']])->get()->getResultArray();

			

			/* echo json_encode($productLine);

			var_dump(empty($productLine));

			exit; */

			

			if(empty($manufacturer) && (!empty($product['productDetail']['vendor']) && !is_null($product['productDetail']['vendor']))){

				$db->table('manufacturer_ca')->insert([

													'manufacturer' => $product['productDetail']['vendor'],

													'slug' => slugify($product['productDetail']['vendor']),

													'created_at' => returnDate(returnAccess()['zone']['ca'],"Y-m-d H:i")]);

				if ($db->transStatus() === FALSE) {

					$db->transRollback();

				} else {

					$db->transCommit();

					$manufacturer = $db->table('manufacturer_ca')->where(['manufacturer' =>  $product['productDetail']['vendor']])->get()->getResultArray();

					$db->table('manufacturer_ca')->update([

						'id_marca_ingram' => $manufacturer[0]['id']],["id"=>$manufacturer[0]['id']]);

					if ($db->transStatus() === FALSE) {

						$db->transRollback();

					} else {

						$db->transCommit();

					}

				}			

			}



			if(empty($category) &&  (!empty($product['productDetail']['category']) && !is_null($product['productDetail']['category']))){

				$db->table('categorias_ca')->insert([

													'name' => $product['productDetail']['category'],

													'slug' => slugify($product['productDetail']['category']),

													'parent' => 0,

													'type' => 'category-product',

													'parent_id' => 0,

													'nombre' => $product['productDetail']['category']]);

				if ($db->transStatus() === FALSE) {

					$db->transRollback();

				} else {

					$db->transCommit();

					$category = $db->table('categorias_ca')->where(['name' => $product['productDetail']['category'], 'type' => 'category-product'])->get()->getResultArray();

					

					$db->table('categories_ca')->insert([

						'category' => $product['productDetail']['category'],

						'slug' => slugify($product['productDetail']['category']),

						'id_categoria_ingram' => $category[0]['id'],

						'parent' => 0,

						'type' => 'category-product',

						'parent_id' => 0,

						'nombre' => $product['productDetail']['category']]);

					if ($db->transStatus() === FALSE) {

						$db->transRollback();

					} else {

						$db->transCommit();

					}

				}			

			}



			if(empty($subCategory)&&(!empty($product['productDetail']['subCategory'])&&!is_null($product['productDetail']['subCategory']))){

				$db->table('categorias_ca')->insert([

														'name' => $product['productDetail']['subCategory'],

														'slug' => slugify($product['productDetail']['subCategory']),

														'parent' => $category[0]['id'],

														'type' => 'sub-product',

														'parent_id' => $category[0]['id'],

														'nombre' => $product['productDetail']['subCategory']]);

				if ($db->transStatus() === FALSE) {

					$db->transRollback();

				} else {

					$db->transCommit();

					$subCategory = $db->table('categorias_ca')->where(['name' => $product['productDetail']['subCategory'], 'type' => 'sub-product'])->get()->getResultArray();

					

					$db->table('categories_ca')->insert([

						'category' => $product['productDetail']['subCategory'],

						'slug' => slugify($product['productDetail']['subCategory']),

						'id_categoria_ingram' => $subCategory[0]['id'],

						'parent' => $category[0]['id'],

						'type' => 'sub-product',

						'parent_id' => $category[0]['id'],

						'nombre' => $product['productDetail']['subCategory']]);

						if ($db->transStatus() === FALSE) {

							$db->transRollback();

						} else {

							$db->transCommit();

						}	

				}				

			}



			if(empty($productLine)&&(!empty($product['productDetail']['productLine'])&&!is_null($product['productDetail']['productLine']))){

				$db->table('categorias_ca')->insert([

														'name' => $product['productDetail']['productLine'],

														'slug' => slugify($product['productDetail']['productLine']),

														'parent' => $subCategory[0]['id'],

														'type' => 'line-product',

														'parent_id' => $subCategory[0]['id'],

														'nombre' => $product['productDetail']['productLine']]);

				if ($db->transStatus() === FALSE) {

					$db->transRollback();

				} else {

					$db->transCommit();

					$productLine = $db->table('categorias_ca')->where(['name' => $product['productDetail']['productLine'], 'type' => 'line-product'])->get()->getResultArray();

					

					$db->table('categories_ca')->insert([

						'category' => $product['productDetail']['productLine'],

						'slug' => slugify($product['productDetail']['productLine']),

						'id_categoria_ingram' => $productLine[0]['id'],

						'parent' => $subCategory[0]['id'],

						'type' => 'line-product',

						'parent_id' => $subCategory[0]['id'],

						'nombre' => $product['productDetail']['productLine']]);

					if ($db->transStatus() === FALSE) {

						$db->transRollback();

					} else {

						$db->transCommit();

					}

				}			

			}



			$id_category = (isset($category[0]['id'])?$category[0]['id']:null);

			$id_subcategory = (isset($subCategory[0]['id'])?$subCategory[0]['id']:null);

			$id_line = (isset($productLine[0]['id'])?$productLine[0]['id']:null);

			$id_manufacturer = (isset($manufacturer[0]['id'])?$manufacturer[0]['id']:null);



			$db->table('products_ca')->update(['id_manufacturer'=>$id_manufacturer,'id_category_parent'=>$id_category,'id_category'=>$id_subcategory,'id_line'=>$id_line], ['sku' => $item['sku']]);



			if ($db->transStatus() === FALSE) {

				$db->transRollback();

			} else {

				$db->transCommit();

				if($reset){

					$object_p = json_decode(json_encode($product));

					extractContents($item['sku'],'ca',$object_p);

				}

			}

			$response[$item['sku']]=['id_manufacturer'=>$id_manufacturer,'id_category'=>$id_category,'id_subcategory'=>$id_subcategory,'id_line'=>$id_line];					

		}

		//if($count>100){break;}

	}

	return $response;

}

function RCmx($sku = null, $reset = false)
{

	try{
		$response = null;
		$db       = \Config\Database::connect();

		if (!$reset) {

			if (!is_null($sku)) {

				$sku = !is_array($sku) ? [$sku] : $sku;



				$skus = $db->table('products_mx')

				->select('sku')

				->where('id_category is null or id_category = \'\' or id_manufacturer is null or id_manufacturer = \'\'')

					->where('sku in (\'' . implode("','", $sku) . '\')')

					->get()->getResultArray();
			} else {

				$skus = $db->table('products_mx')

				->select('sku')

				->where('id_category is null or id_category = \'\' or id_manufacturer is null or id_manufacturer = \'\'')

					->get()->getResultArray();
			}
		} else {

			if (!is_null($sku)) {

				$sku = !is_array($sku) ? [$sku] : $sku;

				$skus = $db->table('products_mx')

				->select('sku')

				->where('sku in (\'' . implode("','", $sku) . '\')')

					->get()->getResultArray();
			} else {

				$skus = $db->table('products_mx')->select('sku')->get()->getResultArray();
			}
		}





		if (!is_null($sku)) {

			$sku = !is_array($sku) ? [$sku] : $sku;

			$skus  = array_filter(
				$skus,

				function ($item) use ($sku) {

					return in_array($item['sku'], $sku);
				}

			);
		}



		////////////////////////////////////////////////////////////////////////////////////////

		//igualar categories => categorias//////////////////////////////////////

		///////////////////////////////////////////////////////////////////////////////////////

		$count = 0;
		
		foreach ($skus as $item) {

			$count++;

			if (file_exists('./getContents/mx/' . $item['sku'] . '.json')) {



				$product = json_decode(file_get_contents('./getContents/mx/' . $item['sku'] . '.json'), true);


				if (
					empty($product['productDetail']['category']) || is_null($product['productDetail']['category']) ||

					empty($product['productDetail']['vendor']) || is_null($product['productDetail']['vendor'])
				) {

					$productFail = (file_exists('./getContents/mx_product_json_fail.json')) ? json_decode(file_get_contents('./getContents/mx_product_json_fail.json'), true) : [];

					$productFail[] = $item['sku'];

					file_put_contents('./getContents/mx_product_json_fail.json', json_encode(array_unique($productFail)));

					continue;
				}



				$category = $db->table('categorias_mx')->where(['name' => $product['productDetail']['category'], 'type' => 'category-product'])->get()->getResultArray();

				$subCategory = $db->table('categorias_mx')->where(['name' => $product['productDetail']['subCategory'], 'type' => 'sub-product'])->get()->getResultArray();

				$productLine = $db->table('categorias_mx')->where(['name' => $product['productDetail']['productLine'], 'type' => 'line-product'])->get()->getResultArray();

				$manufacturer = $db->table('manufacturer_mx')->where(['manufacturer' => $product['productDetail']['vendor']])->get()->getResultArray();



				



				if (empty($manufacturer) && (!empty($product['productDetail']['vendor']) && !is_null($product['productDetail']['vendor']))) {

					$db->table('manufacturer_mx')->insert([

						'manufacturer' => $product['productDetail']['vendor'],

						'slug' => slugify($product['productDetail']['vendor']),

						'created_at' => returnDate(returnAccess()['zone']['mx'], "Y-m-d H:i")
					]);

					if ($db->transStatus() === FALSE) {

						$db->transRollback();
					} else {

						$db->transCommit();

						$manufacturer = $db->table('manufacturer_mx')->where(['manufacturer' =>  $product['productDetail']['vendor']])->get()->getResultArray();

						$db->table('manufacturer_mx')->update([

							'id_marmx_ingram' => $manufacturer[0]['id']
						], ["id" => $manufacturer[0]['id']]);

						if ($db->transStatus() === FALSE) {

							$db->transRollback();
						} else {

							$db->transCommit();
						}
					}
				}

				if (empty($category) &&  (!empty($product['productDetail']['category']) && !is_null($product['productDetail']['category']))) {
					/* echo json_encode([

						'name' => $product['productDetail']['category'],

						'slug' => slugify($product['productDetail']['category']),

						'parent' => 0,

						'type' => 'category-product',

						'parent_id' => 0,

						'nombre' => $product['productDetail']['category']
					]);
					exit; */
					$db->table('categorias_mx')->insert([

						'name' => $product['productDetail']['category'],

						'slug' => slugify($product['productDetail']['category']),

						'parent' => 0,

						'type' => 'category-product',

						'parent_id' => 0,

						'nombre' => $product['productDetail']['category']
					]);

					if ($db->transStatus() === FALSE) {

						$db->transRollback();
					} else {

						$db->transCommit();

						$category = $db->table('categorias_mx')->where(['name' => $product['productDetail']['category'], 'type' => 'category-product'])->get()->getResultArray();

						

						$db->table('categories_mx')->insert([

							'category' => $product['productDetail']['category'],

							'slug' => slugify($product['productDetail']['category']),

							'id_categoria_ingram' => $category[0]['id'],

							'parent' => 0,

							'type' => 'category-product',

							'parent_id' => 0,

							'nombre' => $product['productDetail']['category']
						]);

						if ($db->transStatus() === FALSE) {

							$db->transRollback();
						} else {

							$db->transCommit();
						}
					}
				}



				if (empty($subCategory) && (!empty($product['productDetail']['subCategory']) && !is_null($product['productDetail']['subCategory']))) {

					$db->table('categorias_mx')->insert([

						'name' => $product['productDetail']['subCategory'],

						'slug' => slugify($product['productDetail']['subCategory']),

						'parent' => $category[0]['id'],

						'type' => 'sub-product',

						'parent_id' => $category[0]['id'],

						'nombre' => $product['productDetail']['subCategory']
					]);

					if ($db->transStatus() === FALSE) {

						$db->transRollback();
					} else {

						$db->transCommit();

						$subCategory = $db->table('categorias_mx')->where(['name' => $product['productDetail']['subCategory'], 'type' => 'sub-product'])->get()->getResultArray();



						$db->table('categories_mx')->insert([

							'category' => $product['productDetail']['subCategory'],

							'slug' => slugify($product['productDetail']['subCategory']),

							'id_categoria_ingram' => $subCategory[0]['id'],

							'parent' => $category[0]['id'],

							'type' => 'sub-product',

							'parent_id' => $category[0]['id'],

							'nombre' => $product['productDetail']['subCategory']
						]);

						if ($db->transStatus() === FALSE) {

							$db->transRollback();
						} else {

							$db->transCommit();
						}
					}
				}



				if (empty($productLine) && (!empty($product['productDetail']['productLine']) && !is_null($product['productDetail']['productLine']))) {

					$db->table('categorias_mx')->insert([

						'name' => $product['productDetail']['productLine'],

						'slug' => slugify($product['productDetail']['productLine']),

						'parent' => $subCategory[0]['id'],

						'type' => 'line-product',

						'parent_id' => $subCategory[0]['id'],

						'nombre' => $product['productDetail']['productLine']
					]);

					if ($db->transStatus() === FALSE) {

						$db->transRollback();
					} else {

						$db->transCommit();

						$productLine = $db->table('categorias_mx')->where(['name' => $product['productDetail']['productLine'], 'type' => 'line-product'])->get()->getResultArray();



						$db->table('categories_mx')->insert([

							'category' => $product['productDetail']['productLine'],

							'slug' => slugify($product['productDetail']['productLine']),

							'id_categoria_ingram' => $productLine[0]['id'],

							'parent' => $subCategory[0]['id'],

							'type' => 'line-product',

							'parent_id' => $subCategory[0]['id'],

							'nombre' => $product['productDetail']['productLine']
						]);

						if ($db->transStatus() === FALSE) {

							$db->transRollback();
						} else {

							$db->transCommit();
						}
					}
				}



				$id_category = (isset($category[0]['id']) ? $category[0]['id'] : null);

				$id_subcategory = (isset($subCategory[0]['id']) ? $subCategory[0]['id'] : null);

				$id_line = (isset($productLine[0]['id']) ? $productLine[0]['id'] : null);

				$id_manufacturer = (isset($manufacturer[0]['id']) ? $manufacturer[0]['id'] : null);



				$db->table('products_mx')->update(['id_manufacturer' => $id_manufacturer, 'id_category_parent' => $id_category, 'id_category' => $id_subcategory, 'id_line' => $id_line], ['sku' => $item['sku']]);



				if ($db->transStatus() === FALSE) {

					$db->transRollback();
				} else {

					$db->transCommit();

					if ($reset) {

						$object_p = json_decode(json_encode($product));

						extractContents($item['sku'], 'mx', $object_p);
					}
				}

				$response[$item['sku']] = ['id_manufacturer' => $id_manufacturer, 'id_category' => $id_category, 'id_subcategory' => $id_subcategory, 'id_line' => $id_line];
			}

			//if($count>100){break;}

		}
		echo json_encode($response);

		return $response;
	}catch (Exception $e) {
       
        echo $e;    
    }
}



function scrapping($contents,$seed){

	$string_to = [];

	while(strpos($contents,$seed["parent-1"])){

		$contents = substr($contents, strpos($contents,$seed["component"])+strlen($seed["component"]));

		$contents = substr($contents, strpos($contents,$seed["parent-1"])+strlen($seed["parent-1"])); 

		$string_do = substr($contents, strpos($contents,$seed["parent-2"])+strlen($seed["parent-2"]));            

		$pos_end = strpos($string_do,$seed["finish"]);           



		$string_to_ = substr($string_do,0,$pos_end);



		$string_to[] =  utf8_encode($string_to_);

		$contents = substr($string_do,strpos($string_do,$seed["parent-2"])+strlen($seed["parent-2"]));

		if($seed["type"]=="unit"){

			break 1;

		}	

	}

	return (count($string_to)>0)?$string_to:[null];

}



function get_contents_productsJson($sku,$country){

	return (file_exists('./getContents/'.$country.'/'.$sku.'.json'))?

			read_file_json('./getContents/'.$country.'/'.$sku.'.json')

	:false;

}







function extractContents($sku,$country,$object_p = null){	



	//$object_p = json_decode(get_contents_products($sku,$country)['json_object']);

	if(is_null($object_p)){

		$object_p = json_decode(get_contents_productsJson($sku,$country));

	}

	

	$contentsHTML = null;

	$contentsJson = null;

	$contentsImages = null;

	$contentsTitle = null;



	



	if($object_p){

		//var_dump($object_p->productDetail->basicSpecifications);

		$fileContents=[];

		if(file_exists('./getContents/'.$country.'/'.'contents.json')){

				//$fileContents = read_file_json('./getContents/'.$country.'/'.'contents.json',true);

		}

		$table_tr = null;

		if(isset($object_p->productDetail->basicSpecifications)&&!is_null($object_p->productDetail->basicSpecifications)){

			$table_tr .= '<table>';

			foreach ($object_p->productDetail->basicSpecifications as $element) {

				$cabecera_description = isset($element->subHeading) ? $element->subHeading : '';

				$table_tr .= '<tr><td colspan="2">' . $cabecera_description . '</td></tr>';

				if ($element->productSpecifications) {

					foreach ($element->productSpecifications as $item) {

						$table_tr .= '<tr>';

						$table_tr .= '<td>' . $item->key . '</td>';

						$table_tr .= '<td>' . $item->value . '</td>';

						$table_tr .= '</tr>';

					}

				}

			}

			$table_tr .=  '</table>';

			$contentsHTML = $table_tr;

			$contentsJson = $object_p->productDetail->basicSpecifications;

		}		

		if(isset($object_p->productDetail->basicSpecificationsHTML)){
			if(!empty($object_p->productDetail->basicSpecificationsHTML)||!is_null($object_p->productDetail->basicSpecificationsHTML)){
				$contentsHTML = $object_p->productDetail->basicSpecificationsHTML;
			}
		}

		if (!is_null($object_p) && isset($object_p->productDetail->productImage)) {
			$contentsImages = ["imageGalleryUrlHigh"=>explode(",",$object_p->productDetail->productImage->imageGalleryUrlHigh),"images"=>$object_p->productDetail->productImage];
		}

		if (!is_null($object_p) && isset($object_p->productDetail->title)) {

			$contentsTitle = $object_p->productDetail->title ." ". $object_p->productDetail->description;

		}



/* var_dump($fileContents[$sku]);

		exit; */



		if(!isset($fileContents[$sku])){

			$fileContents[$sku] = [

				"contentsHTML"=>$contentsHTML,

				"contentsJson"=>$contentsJson,

				"contentsImages"=>$contentsImages,

				"contentsTitle"=>$contentsTitle];

		}



		



		$ProductWeight=(!is_null($object_p->productDetail->productMeasurement->pMeasureWeight)?number_format($object_p->productDetail->productMeasurement->pMeasureWeight, 2, '.', ''):null);       

		$ProductLength=(!is_null($object_p->productDetail->productMeasurement->pMeasureLength)?number_format($object_p->productDetail->productMeasurement->pMeasureLength, 2, '.', ''):null);

		$ProductWidth=(!is_null($object_p->productDetail->productMeasurement->pMeasureWidth)?number_format($object_p->productDetail->productMeasurement->pMeasureWidth, 2, '.', ''):null);

		$ProductHeight=(!is_null($object_p->productDetail->productMeasurement->pMeasureHeight)?number_format($object_p->productDetail->productMeasurement->pMeasureHeight, 2, '.', ''):null);



		$fileContents[$sku] = [

								"contentsTitle"=>(is_null($fileContents[$sku]['contentsTitle'])?$contentsTitle:$fileContents[$sku]['contentsTitle']),

								"contentsHTML"=>(is_null($fileContents[$sku]['contentsHTML'])?$contentsHTML:$fileContents[$sku]['contentsHTML']),

								"contentsJson"=>(is_null($fileContents[$sku]['contentsJson'])?$contentsJson:$fileContents[$sku]['contentsJson']),

								"contentsImages"=>(is_null($fileContents[$sku]['contentsImages'])?$contentsImages:$fileContents[$sku]['contentsImages']),

								"dim"=>[

									'ProductWeight'=>$ProductWeight,        

									'ProductLength'=>$ProductLength,

									'ProductWidth'=>$ProductWidth,

									'ProductHeight'=>$ProductHeight]

							  ];



		write_file_root('./getContents/'.$country.'/','contents.json',json_encode($fileContents,JSON_UNESCAPED_UNICODE));



		$db       = \Config\Database::connect();

		if(!is_null(!$contentsTitle)){

			$db->table('products_'.$country)->update(['title' => $contentsTitle], ['sku' => $sku]);

			if ($db->transStatus() === FALSE) {

				$db->transRollback();

			} else {

				$db->transCommit();

			}

		}

		if(!is_null($contentsHTML)){

			$db->table('products_'.$country)->update(['ficha_tecnica' => $contentsHTML], ['sku' => $sku]);

			if ($db->transStatus() === FALSE) {

				$db->transRollback();

			} else {

				$db->transCommit();

			}

		}



		if(!is_null($contentsImages)){

			$db->table('products_'.$country)->update(['image_product_heigth'=>json_encode($contentsImages,JSON_UNESCAPED_UNICODE)], ['sku' => $sku]);

			if ($db->transStatus() === FALSE) {

				$db->transRollback();

			} else {

				$db->transCommit();

			}

		}

		if(!is_null($contentsJson)){

			$db->table('products_'.$country)->update(['ficha_json'=>json_encode($contentsJson,JSON_UNESCAPED_UNICODE)], ['sku' => $sku]);

			if ($db->transStatus() === FALSE) {

				$db->transRollback();

			} else {

				$db->transCommit();

			}

		}

		if(!is_null($fileContents[$sku]['dim']['ProductWeight'])){

			$db->table('products_'.$country)->update($fileContents[$sku]['dim'], ['sku' => $sku]);

			if ($db->transStatus() === FALSE) {

				$db->transRollback();

			} else {

				$db->transCommit();

			}

		}



	}

	return (isset($fileContents[$sku])?$fileContents[$sku]:null);

}



function get_contents_products($sku,$country='mx'){

	if (!function_exists('url_exists')) {

		function url_exists($url) { 

			$ch = @curl_init($url); 

			@curl_setopt($ch, CURLOPT_HEADER, TRUE); 

			@curl_setopt($ch, CURLOPT_NOBODY, TRUE); 

			@curl_setopt($ch, CURLOPT_FOLLOWLOCATION, FALSE);

			@curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

			$status = array(); preg_match('/HTTP\/.* ([0-9]+) .*/', @curl_exec($ch) , $status);

			//var_dump($status);

			//var_dump($url);

			return isset($status[1])?($status[1] == 200):false;		

		}

	}

	global $getUrl;

	$response_object = [];

	// Retrieve the standard HTML parsing array for later use.

	$htmloptions = TagFilter::GetHTMLOptions();



	// Retrieve a URL (emulating Firefox by default).

	$url = returnIngramUrl()["getProduct"][$country].$sku;

	sleep(10);

	$web = new WebBrowser();

	$result = $web->Process($url);

	

	$response_object['status']='Success';

	// Check for connectivity and response errors.

	if (!$result["success"])

	{

		$response_object['status']= "Error retrieving URL.  " . $result["error"];

		return $response_object;

		//exit();

	}



	if ($result["response"]["code"] != 200)

	{

		$response_object['status']= "Error retrieving URL.  Server returned:  " . $result["response"]["code"] . " " . $result["response"]["meaning"] ;

		return $response_object;			

		//exit();

	}



	// Get the final URL after redirects.

	$baseurl = $result["url"];

	// Use TagFilter to parse the content.

	$html = TagFilter::Explode($result["body"], $htmloptions);

	$root = $html->Get();

	$result2 = $html->Find("img.img-responsive");

	if (!$result2["success"])

	{

		$response_object['status']= "Error parsing/finding URLs.  " . $result2["error"];

		return $response_object;

		//exit();

	}

	

	$array_img = [];

	foreach ($result2["ids"] as $id)

	{

		// Faster direct access.

		//echo "\t" . $html->nodes[$id]["attrs"]["src"] . "\n";

		$img_src = HTTP::ConvertRelativeToAbsoluteURL($baseurl, $html->nodes[$id]["attrs"]["src"]);

		if(!in_array($img_src,$array_img)){

			$array_img[] = $img_src;

		}

	}

	$response_object['images']=$array_img;



	$response_object['categoria']='';

	$rows = $root->Find("div.blog-main span a");	

	foreach ($rows as $row)

	{

		$response_object['categoria']= $row->GetOuterHTML();

	}

	$response_object['marca']='';

	$rows = $root->Find("div.Top-Sku-VPN-UPC div span a");

	foreach ($rows as $row)

	{

		$response_object['marca']= $row->GetOuterHTML();

	}		

	

	$html = $result["body"];

	//echo $html."<br><br>";

	//var_dump($result);



	if(in_array($country,['mx','ca'])){

		$response_object['urlGetPDF'] = [];



		$pos = strpos($html, "{\"productDetail\":");

		$resultadofinal = substr($html, $pos-1, strlen($html));

		$pos2 = strpos($resultadofinal, "};");

		$resultadofinal2 = substr($resultadofinal, 1, $pos2);    

		$response_object['json_object'] = $resultadofinal2;		

	}



	if(in_array($country,['co'])){

		$images=scrapping($html,["type"=>"unit","component"=>"input","parent-1"=>"hdn-ImageGalleryURLHigh","parent-2"=>"value=\"","finish"=>"\""]);

		

		$images_=[];

		if(count($images)>0){

			foreach(explode(",",$images[0]) as $item){

				if(strpos($item,"no-photo")===false && strpos( $item,"gif")===false && url_exists($item)){

					$images_[]=$item;

				}

			}

		}



		$response['productDetail']["productImage"]["imageGalleryUrlHigh"] = ((count($images_)>0)?implode(",",$images_):null);

		//description

		$description = scrapping($html,["type"=>"unit","component"=>"span","parent-1"=>"ProductDetailsDescriptionControl_lab_AbridgedDescription\"","parent-2"=>">","finish"=>"</span>"])[0];

		if(is_null($description)){$description = scrapping($html,["type"=>"unit","component"=>"span","parent-1"=>"ProductDetailsDescriptionControl_lab_FullDescription\"","parent-2"=>">","finish"=>"</span>"])[0];}

		if(!is_null($description)&&!empty(trim($description))){

				$response['productDetail']["basicSpecifications"][] = (!empty(trim($description)))?[

																			"subHeading"=>null,

																			"productSpecifications"=>[

																										[

																											"key"=>"Información de Marketing",

																											"value"=>$description

																										]

																									]

																		]:null;

		}else{

			$response['productDetail']["basicSpecifications"] = null;

		}

		//category

		$response['productDetail']["category"] = scrapping($html,["type"=>"unit","component"=>"<a href=","parent-1"=>"CatSearch\"","parent-2"=>">","finish"=>"</a>"])[0];    

		//subcategory

		$response['productDetail']["subCategory"] = scrapping($html,["type"=>"unit","component"=>"<a href=","parent-1"=>"SubCatSearch\"","parent-2"=>">","finish"=>"</a>"])[0];

		//productLine

		$response['productDetail']["productLine"] = scrapping($html,["type"=>"unit","component"=>"<a href=","parent-1"=>"ProdLineSearch\"","parent-2"=>">","finish"=>"</a>"])[0];

		//vendor

		$response['productDetail']["vendor"] = scrapping($html,["type"=>"unit","component"=>"<a href=","parent-1"=>"product-details-vendor\"","parent-2"=>">","finish"=>"</a>"])[0];

		//title

		$response['productDetail']["title"] = scrapping($html,["type"=>"unit","component"=>"span","parent-1"=>"_lab_Title\"","parent-2"=>">","finish"=>"</span>"])[0];

		

		$response_object['urlGetPDF'] = [];

		//$response_object['json_object'] = $response;

		$response_object['json_object'] = json_encode($response,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);

		/*echo json_encode($response)."<br><br>";

		echo json_encode($response_object['json_object'])."<br><br>"; */

	}

	//var_dump($response_object);

	$validResponse = json_decode($response_object['json_object'],true);

	if((is_null($validResponse['productDetail']["category"])&&is_null($validResponse['productDetail']["subCategory"]))||is_null($validResponse['productDetail']["vendor"])){

		$response_object['json_object']=null;

	}

	return $response_object;

}



function get_contents_all($country='mx'){

	global $getUrl;	

	$objSKU= [];

	$file_concat = "";

	$banLoop = true;

	$x=0;

	while($banLoop){

		$x++;

		// Send a POST request to a URL.

		$web = new WebBrowser();

		$options = array(

			"postvars" => array(

					"Mode"=> 12,

					"Term"=>$x,

					"DeselectedTerm"=>"", 

					"State"=> "All",

					"Range"=> "",

					"SortMode"=> 0,

					"RecordPerPage"=> 50,

					"PageLayout"=> 0,

					"SortResultBy"=>"", 

					"Page"=> 0,

					"PageZoneSearchState"=>"", 

					"CurrentCrossSellPage"=> 0,

					"CurrentCrossSellSkus"=> "",

					"ExchangeRate"=> 20.17,

					"OffSet"=> 0,

					"TechSpecDataForHash"=>"" 

			)

		);



		$result = $web->Process($getUrl["getProducts"][$country], $options);



		if (!$result["success"])

		{

			$file_concat .= "Error retrieving URL.  " . $result["error"] . "\n";

		}



		if ($result["response"]["code"] != 200)

		{

			$file_concat .= "Error retrieving URL.  Server returned:  " . $result["response"]["code"] . " " . $result["response"]["meaning"] . "\n";

		}

		

		$pos = strpos($result['body'], "{\"productList\":");

		if ($pos === false) {

			$banLoop = false;

		} else {

			$file_concat .= $result['body'];

			$html = $result['body'];

			$pos = strpos($html , "{\"productList\":");

			$resultadofinal = substr($html, $pos+14, strlen($html));

			$pos2 = strpos($resultadofinal, "]}");

			$resultadofinal2 = substr($resultadofinal, 1, $pos2);    

			$out_skus = $resultadofinal2;                

		

			$objSKU = array_merge($objSKU,json_decode($out_skus));



			if(in_array("get_product",$getUrl["filter"])){

				foreach(json_decode($out_skus) as $item){

					if(get_action_contents($item,$country)){

						$productResponse = get_contents_products($item,$country);

						$obj_json = json_decode($productResponse["json_object"]);

						if($obj_json){

							set_product_contents($item,$obj_json,$productResponse["urlGetPDF"],$country);

						}

					}					

				}				

			}

		}					

	}



	$list_skus = array_column($objSKU,"sku");

	return $list_skus;

}



function get_action_contents($sku,$country='mx'){

	global $mysqli;

	$q = "SELECT * FROM product_".$country." WHERE (ficha_tecnica is null or ficha_tecnica = '' or image_product_heigth is null or image_product_heigth = '') and sku ='" . trim($sku) . "'";

	$r_contents = $mysqli->query($q);

	return $r_contents->num_rows > 0;

}



function get_clear_contents($count=null,$country='mx',$type='all'){

	global $mysqli;

	$filter=(($country=='co')?"or image_product_heigth like '%noimage%' or image_product_heigth like '%no-photo%'":'');

	if($type=='all'){

		$q = "SELECT sku FROM product_".$country." WHERE (ficha_tecnica is null or ficha_tecnica = '' or image_product_heigth is null or image_product_heigth = '' ".$filter.") ORDER BY id DESC LIMIT ".((!is_null($count))?$count:"2000");

	}

	if($type=='imagenes'){

		$q = "SELECT sku FROM product_".$country." WHERE image_product_heigth is null or image_product_heigth = '' " .$filter." ORDER BY id DESC LIMIT ".((!is_null($count))?$count:"2000");

	}

	if($type=='fichas'){

		$q = "SELECT sku FROM product_".$country." WHERE ficha_tecnica is null or ficha_tecnica = '' ORDER BY id DESC LIMIT ".((!is_null($count))?$count:"2000");

	}

	$r_contents = $mysqli->query($q);	

	$arr = [];

	while ($r = $r_contents->fetch_assoc()) {

		$arr[]= $r['sku'];

	}

	return $arr;

}



function set_product_contents($name,$object_p,$urlGetPDF,$country='mx'){	

	global $mysqli;

	global $result_fichas;

	global $result_imagenes;

	global $result;

	global $getUrl;



	if (!function_exists('url_exists')) {

		function url_exists($url) { 

			$ch = @curl_init($url); 

			@curl_setopt($ch, CURLOPT_HEADER, TRUE); 

			@curl_setopt($ch, CURLOPT_NOBODY, TRUE); 

			@curl_setopt($ch, CURLOPT_FOLLOWLOCATION, FALSE);

			@curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

			$status = array(); preg_match('/HTTP\/.* ([0-9]+) .*/', @curl_exec($ch) , $status);

			return ($status[1] == 200);		

		}

	}



	$result[] = $name;



	$q = "SELECT * FROM product_".$country." WHERE sku ='" . $name . "'";

	$r_contents = $mysqli->query($q);

	if ($r_contents->num_rows == 0) {

		$q = "INSERT product_".$country." (sku) VALUES ('" . trim($name) . "')";

		$mysqli->query($q);

		$product_id = $mysqli->insert_id;

	} else {

		$product_id = $r_contents->fetch_assoc()['id'];

	}



	$id_cat = 0;

	$id_marca = 0;

	$id_marca_set = 0;

	$id_marca_mx=0;

	$id_subcat = 0;

	$id_subcat_set = 0;

	$id_subcat_line = 0;

	$name_cat = '';

	$name_subcat = '';

	$name_marca = '';

	$name_line = '';



	if(in_array("rc",$getUrl["filter"])||in_array("all",$getUrl["filter"])){

		if (!is_null($object_p) && isset($object_p->productDetail->category) && !is_null($object_p->productDetail->category)) {



			$name_cat = $object_p->productDetail->category;



			$q = "SELECT * FROM categories_".$country." WHERE type = 'category-product' and parent = 0 and slug = '" . slugify($object_p->productDetail->category) . "'";

			

			$result_query = $mysqli->query($q);

			if ($result_query->num_rows == 0) {

				$q = "INSERT categories_".$country." (type,name,slug,parent) VALUES ('category-product','" . $object_p->productDetail->category . "','" . slugify($object_p->productDetail->category) . "',0)";

				

				$mysqli->query($q);

				$id_cat = $mysqli->insert_id;

			} else {

				$id_cat = $result_query->fetch_assoc()['id'];

			}

			

			if (!is_null($object_p->productDetail->subCategory) && isset($object_p->productDetail->subCategory)) {

				$name_subcat = $object_p->productDetail->subCategory;

				$q = "SELECT * FROM categories_".$country." WHERE type = 'sub-product' and parent > 0 and slug = '" . slugify($object_p->productDetail->subCategory) . "'";

				$result_query = $mysqli->query($q);

				if ($result_query->num_rows == 0) {

					$q = "INSERT categories_".$country." (type,name,slug,parent) VALUES ('sub-product','" . $object_p->productDetail->subCategory . "','" . slugify($object_p->productDetail->subCategory) . "',$id_cat)";

					

					$mysqli->query($q);

					$id_subcat = $mysqli->insert_id;

				} else {

					$id_subcat = $result_query->fetch_assoc()['id'];

				}

			}



			if (!is_null($object_p->productDetail->productLine) && isset($object_p->productDetail->productLine)) {

				$name_line = $object_p->productDetail->productLine;

				$q = "SELECT * FROM categories_".$country." WHERE type = 'line-product' and parent > 0 and slug = '" . slugify($object_p->productDetail->productLine) . "'";

				$result_query = $mysqli->query($q);

				if ($result_query->num_rows == 0) {

					$q = "INSERT categories_".$country." (type,name,slug,parent) VALUES ('line-product','" . $object_p->productDetail->productLine . "','" . slugify($object_p->productDetail->productLine) . "',$id_subcat)";

					

					$mysqli->query($q);

					$id_subcat_line = $mysqli->insert_id;

				} else {

					$id_subcat_line = $result_query->fetch_assoc()['id'];

				}

			}



			$id_subcat_set = (($id_subcat>0)?$id_subcat:(($id_cat>0)?$id_cat:0));

			if ($id_subcat_set > 0) {

				$q = "SELECT * FROM categories_".$country." WHERE id =".$id_subcat_set;

				$result_query = $mysqli->query($q);

				$id_subcat_set_ = $result_query->fetch_assoc()['id_categoria_ingram'];

				if ($id_subcat_set_ > 0) {			

					$url = 'http://'.$country.'.bdicentralserver.com/api/set_product';

					$ch = curl_init($url);

					$data = json_encode(

											[

												"data"=>["sku"=>$name,"id_subcategoria"=>$id_subcat_set_],

												"type"=>"categoriasUni"

											],

											JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES

										);

					curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

					curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));

					curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

					$response = curl_exec($ch);

					curl_close($ch);

				}else{

					//send email status//////////////////////////////////////////////

					/////////////////////////////////////////////////////////////////

				}

			}else{

				//send email status//////////////////////////////////////////////

				/////////////////////////////////////////////////////////////////

			}

		}

		

		if (!is_null($object_p) && isset($object_p->productDetail->vendor) && !is_null($object_p->productDetail->vendor)) {

			$name_marca = $object_p->productDetail->vendor;

			

			$q = "SELECT * FROM manufacturer_".$country." WHERE marca = '" . $object_p->productDetail->vendor . "'";

			$result_query = $mysqli->query($q);

			if ($result_query->num_rows == 0) {

				$q = "INSERT manufacturer_".$country." (marca) VALUES ('" . $object_p->productDetail->vendor . "')";

				$mysqli->query($q);

				$id_marca_mx = $mysqli->insert_id;

				$url = 'http://'.$country.'.bdicentralserver.com/api/set_product';

				$ch = curl_init($url);

				$data = json_encode(

									[

										"data"=>["name"=>$object_p->productDetail->vendor,"slug"=>slugify($object_p->productDetail->vendor)],

										"type"=>"marcasInsert"

									],JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES

									);

				curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

				curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));

				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

				$response = curl_exec($ch);

				curl_close($ch);

			} else {

				$id_marca_mx = $result_query->fetch_assoc()['id'];	

						

			}



			$q = "SELECT * FROM contents_marcas_".$country." WHERE slug = '" . slugify($object_p->productDetail->vendor) . "'";

			$result_query = $mysqli->query($q);

			if ($result_query->num_rows == 0) {

				$q = "INSERT contents_marcas_".$country." (name,slug,id_marca_ingram) VALUES ('" . $object_p->productDetail->vendor . "','" . slugify($object_p->productDetail->vendor) . "',".$id_marca_mx.")";

				$mysqli->query($q);

				$id_marca = $mysqli->insert_id;

			} else {

				$result = $result_query->fetch_assoc();

				$id_marca = $result['id'];	

				$id_marca_ingram = $result['id_marca_ingram'];		

				if(empty($id_marca_ingram)||is_null($id_marca_ingram)){

					$q = "UPDATE contents_marcas_".$country." SET id_marca_ingram=".$id_marca_mx." WHERE id=".$id_marca;

					$mysqli->query($q);

				}

			}

			$url = 'http://'.$country.'.bdicentralserver.com/api/set_product';

			$ch = curl_init($url);

			

			$data = json_encode(

								[

									"data"=>["sku"=>$name,"id_marca"=>$id_marca_mx,"name"=>$object_p->productDetail->vendor,"slug"=>slugify($object_p->productDetail->vendor)],

									"type"=>"marcasUni"

								],JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES

								);

			curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

			curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));

			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

			$response = curl_exec($ch);	

			curl_close($ch);

		}



		if ($id_marca != 0 && $id_cat != 0) {

			$q = "UPDATE product_".$country." SET name_subcategoria='" . $name_subcat . "', name_marca='" . $name_marca . "', name_categoria='" . $name_cat . "', name_linea='" . $name_line . "', id_marca=" . $id_marca . ", id_subcategoria=" . $id_subcat . ", id_categoria=" . $id_cat . ", id_line=" . $id_subcat_line . " WHERE id=" . $product_id;

			$mysqli->query($q);

		}



		if (!is_null($object_p) && isset($object_p->productDetail->title)) {

			$q = "UPDATE product_".$country." SET title='" . $object_p->productDetail->title . "' WHERE id=" . $product_id;

			$mysqli->query($q);

		}

	}

	

	if(in_array("contents",$getUrl["filter"])||in_array("all",$getUrl["filter"])){

		if (!is_null($object_p) && isset($object_p->productDetail->productImage->imageGalleryUrlHigh)) {

			$q = "UPDATE product_".$country." SET image_product_heigth='" . json_encode(explode(",", $object_p->productDetail->productImage->imageGalleryUrlHigh)) . "' WHERE id=" . $product_id;

			$mysqli->query($q);



			$array_set_bdi_imagenes = ["sku"=>trim($name),"images"=>$object_p->productDetail->productImage->imageGalleryUrlHigh];



			$url = 'http://'.$country.'.bdicentralserver.com/api/set_product';

			$ch = curl_init($url);

			$data = json_encode(["type"=>"imagenes","data"=>[$array_set_bdi_imagenes]]);

			curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

			curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));

			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

			$response = curl_exec($ch);

			curl_close($ch);				

			$result_imagenes[] = trim($name);

			file_put_contents("./skus_imagenes.json",json_encode($result_imagenes));

		}	



		if (!is_null($object_p) && isset($object_p->productDetail->basicSpecifications)) {		

			$table_tr = null;

			/////////////////////////////////////table pdf///////////////

			$refactor = false;

			if(count($urlGetPDF)>0){

				$refactor = true;

				$table_tr = '<table><tr>';

				//$indexPdf  = 0;

				foreach ($urlGetPDF as $element) {

					if(url_exists($element)){

						$actionUrl = "<a target=\"_blank\" href=\"".$element."\"><img src=\"https://bdicentralserver.com/datasheet/pdf/document.png\" alt=\"Document\" style=\"width:25px;height:25px;\"></a>";

						$table_tr .= '<td>' . $actionUrl . '</td>';		

					}

				}

				$table_tr .=  '</tr></table>';

			}



			if(isset($object_p->productDetail->basicSpecifications)&&!is_null($object_p->productDetail->basicSpecifications)){

				$table_tr .= '<table>';

				foreach ($object_p->productDetail->basicSpecifications as $element) {

					$cabecera_description = isset($element->subHeading) ? $element->subHeading : '';

					$table_tr .= '<tr><td colspan="2">' . $cabecera_description . '</td></tr>';

					if (isset($element->productSpecifications)) {

						foreach ($element->productSpecifications as $item) {

							$table_tr .= '<tr>';

							$table_tr .= '<td>' . $item->key . '</td>';

							$table_tr .= '<td>' . $item->value . '</td>';

							$table_tr .= '</tr>';

						}

					}

				}

				$table_tr .=  '</table>';

			}



			$q = "UPDATE product_".$country." SET ficha_tecnica ='" . $table_tr . "', ficha_tecnica_json = '" . json_encode($object_p->productDetail->basicSpecifications,JSON_UNESCAPED_UNICODE) . "' WHERE id = " . $product_id;

			$mysqli->query($q);			

			

			$url = 'http://'.$country.'.bdicentralserver.com/api/set_product';

			$ch = curl_init($url);

			$data = json_encode(

								[

									"type"=>"description",

									"sku"=>trim($name),

									"refactor"=>(isset($_GET['reset']))?true:$refactor,

									"json"=>$object_p->productDetail->basicSpecifications,

									"html"=>$table_tr

								],JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);

			

			curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

			curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));

			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

			$response = curl_exec($ch);

			curl_close($ch);

			$result_fichas[] = trim($name);

			file_put_contents("./skus_fichas.json",json_encode($result_fichas));

		}

	}

}



function get_skus_new($days,$country='mx'){

	$url = 'http://'.$country.'.bdicentralserver.com/api/new_category/'.$days;

	$ch = curl_init($url);

	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

	$response = curl_exec($ch);

	curl_close($ch);

	return json_decode($response);

}

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////

$getSku = [];

if(isset($_GET_['sku'])){

	$getSku = explode(",",$_GET_['sku']);

}



if(isset($_GET_['only_contents'])){

	$getUrl["filter"][]='contents';		

}

if(isset($_GET_['only_rc'])){

	$getUrl["filter"][]='rc';		

}



if(isset($_GET_['only_all'])){

	$getUrl["filter"][]='all';		

}



if(isset($_GET_['all'])){

	if(in_array($_GET_['country'],['mx','co'])){

		if(isset($_GET_['get_product'])){

			$getUrl["filter"][]='get_product';		

		}

		$getSku = get_contents_all($_GET_['country']);

	}

}



if(isset($_GET_['new'])){

	$days = (isset($_GET_['days']))?$_GET_['days']:5;

	$getSku = get_skus_new($days,$_GET_['country']);

}



if(isset($_GET_['clear'])){

	$count = (isset($_GET_['count']))?$_GET_['count']:2000;

	$type_contents=(isset($_GET_['type_contents']))?$_GET_['type_contents']:"";

	$getSku = get_clear_contents($count,$_GET_['country'],$type_contents);	

}



$countSku = 0;

foreach($getSku as $item){

	if( isset($_GET_['reset']) || get_action_contents($item,$_GET_['country']) ){

		$productResponse = get_contents_products($item,$_GET_['country']);

		$obj_json = json_decode($productResponse["json_object"]);

		

		if($obj_json){

			set_product_contents($item,$obj_json,$productResponse["urlGetPDF"],$_GET_['country']);

		}

	}			

	$countSku++;

}

mysqli_close($mysqli);