<?php

namespace App\Models;
use CodeIgniter\Model;
class Storage extends Model
{
	public function getManufacturer($country,$Manufacturer= null){
		if(!is_null($Manufacturer)){
			return $this->db->table('manufacturer_'.$country.' as m')
							->select('null as shop_id,id,manufacturer')
							->orderBy('m.manufacturer','ASC')
							->where('m.id in (\''.implode("','",$Manufacturer).'\')')
							->get()->getResultArray();	
		}else{
			return $this->db->table('manufacturer_'.$country.' as m')
							->select('null as shop_id,id,manufacturer')
							->orderBy('m.manufacturer','ASC')
							->get()->getResultArray();
		}				
	}
	public function getCategories($country,$Categories= null){
		$filter[]="parent_id=0";
		if(!is_null($Categories)){$filter[]='id in (\''.implode("','",$Categories).'\')';}
		$categories =$this->db->table('categorias_'.$country.' as c')
							->select('null as shop_id, id,parent_id,nombre')
							->where(implode(" And ",$filter))
							->orderBy('c.nombre','ASC')
							->get()->getResultArray();	
		$listCategories = [];		
		foreach($categories as $category){
			$filter=["parent_id=".$category["id"]];
			if(!is_null($Categories)){$filter[]='id in (\''.implode("','",$Categories).'\')';}
			$categories_chield = $this->db->table('categorias_'.$country.' as c')
														->select('null as shop_id,id,parent_id,nombre')
														->where(implode(" And ",$filter))
														->orderBy('c.nombre','ASC')
														->get()->getResultArray();
			$listCategories[] = ["parent"=>$category,"child"=>$categories_chield];
		}
		return $listCategories;
	}
	public function countManufacturer($country,$Manufacturer= null,$ProductExclude= null){
		$filter=[];
		if(!is_null($Manufacturer)){$filter[]='p.id_marca in (\''.implode("','",$Manufacturer).'\')';}
		if(!is_null($ProductExclude)){$filter[]='p.sku not in (\''.implode("','",$ProductExclude).'\')';}
		if(count($filter)>0){
			return 
					$this->db->table('products_'.$country.' as p')
					->select('count(*) as count,m.id')				
					->join('manufacturer_'.$country.' as m', 'p.id_marca = m.id')
					->where(implode(" And ",$filter))
					->groupBy('p.id_marca')
					->get()->getResultArray();	
		}else{
			return 
					$this->db->table('products_'.$country.' as p')
					->select('count(*) as count,m.id')				
					->join('manufacturer_'.$country.' as m', 'p.id_marca = m.id')
					->groupBy('p.id_marca')
					->get()->getResultArray();	
		}
	}
	public function countCategories($country,$Categories= null,$ProductExclude= null){
		$filter=[];
		if(!is_null($Categories)){$filter[]='ci.id in (\''.implode("','",$Categories).'\')';}
		if(!is_null($ProductExclude)){$filter[]='p.sku not in (\''.implode("','",$ProductExclude).'\')';}
		if(count($filter)>0){
				return 
						$this->db->table('products_'.$country.' as p')
						->select('
									sum(if(ci.parent_id = 0,1,0)) as countParent,
									sum(if(ci.parent_id > 0,1,0)) as countSub,
									ci.id
								')
						->join('categories_'.$country.' as c', 'p.id_categoria = c.id','left')
						->join('categorias_'.$country.' as ci', 'ci.id = c.id_categoria_ingram','left')
						->where('c.type in (\'category-product\',\'sub-product\')')
						->where(implode(" And ",$filter))
						->groupBy('ci.id')
						->get()->getResultArray();
		}else{
			return 
						$this->db->table('products_'.$country.' as p')
						->select('
									sum(if(ci.parent_id = 0,1,0)) as countParent,
									sum(if(ci.parent_id > 0,1,0)) as countSub,
									ci.id
								')
						->join('categories_'.$country.' as c', 'p.id_categoria = c.id','left')
						->join('categorias_'.$country.' as ci', 'ci.id = c.id_categoria_ingram','left')
						->where('c.type in (\'category-product\',\'sub-product\')')						
						->groupBy('ci.id')
						->get()->getResultArray();
		}
	}
	public function getProducts($country = '', $Manufacturer = null, $Categories = null, $ProductExclude = null)
	{	
		$filter=[];
		if(!is_null($Manufacturer)){$filter[]='p.id_marca in (\''.implode("','",$Manufacturer).'\')';}
		if(!is_null($Categories)){$filter[]='ci.id in (\''.implode("','",$Categories).'\')';}
		if(!is_null($ProductExclude)){$filter[]='p.sku not in (\''.implode("','",$ProductExclude).'\')';}

		$select="							
					sku,
					if(MFRPartNumber is null or MFRPartNumber = \"\",`INV-PRICE-VENDOR-PART`,MFRPartNumber) as partNumber,
					if(title is null or title = \"\",concat(`INV-PRICE-DESC1-1-31`,`INV-PRICE-DESC2`),title) as title,
					m.manufacturer,
					ci.nombre as category,
					m.id as manufacturer_id,
					ci.id as category_id,
					p.`INV-STOCK` as stock_cost,					
					p.`INV-PRICE-RETAIL` as price_retail,
					p.`INV-CUST-COST` as price_cost,					
					if(ProductWidth is null or ProductWidth = \"\",`INV-WIDTH`,ProductWidth) as ProductWidth,
					if(ProductHeight is null or ProductHeight = \"\",`INV-HEIGHT`,ProductHeight) as ProductHeight,
					if(ProductWeight is null or ProductWeight = \"\",`INV-WEIGHT`,ProductWeight) as ProductWeight,
					if(ProductLength is null or ProductLength = \"\",`INV-LENGTH`,ProductLength) as ProductLength,					
					image_product_heigth as images,
					null as stock,
					null as price,
					null as updated_at_image,
					null as ficha_json,
					null as updated_at_json,
					null as ficha_html,
					null as updated_at_html,
					null as updated_at,
					null as prices_reseller,
					null as active_ingram,
					null as message,
					null as warehouse,
					null as updated_at_rc,
					null as shop_id
					
		";	
		$responseProducts = [];
		if(count($filter)>0){
				$products=$this->db->table('products_'.$country.' as p')
									->select($select)
									/* ->join('categories_'.$country.' as c', 'p.id_categoria = c.id')
									->join('categorias_'.$country.' as ci', 'ci.id = c.id_categoria_ingram') */
									//->join('categories_'.$country.' as c', 'p.id_categoria = c.id')
									->join('categorias_'.$country.' as ci', 'ci.id = p.id_category')
									//->join('manufacturer_'.$country.' as m', 'p.id_marca = m.id')
									->join('manufacturer_'.$country.' as m', 'p.id_manufacturer = m.id')
									->where(implode(" And ",$filter))
									->orderBy('m.manufacturer','DESC')
									->orderBy('ci.nombre','DESC')
									->get()->getResultArray();		
				
		}else{
			$products=$this->db->table('products_'.$country.' as p')
									->select($select)
									/* ->join('categories_'.$country.' as c', 'p.id_categoria = c.id')
									->join('categorias_'.$country.' as ci', 'ci.id = c.id_categoria_ingram') */
									//->join('categories_'.$country.' as c', 'p.id_categoria = c.id')
									->join('categorias_'.$country.' as ci', 'ci.id = p.id_category')
									//->join('manufacturer_'.$country.' as m', 'p.id_marca = m.id')
									->join('manufacturer_'.$country.' as m', 'p.id_manufacturer = m.id')
									->orderBy('m.manufacturer','DESC')
									->orderBy('ci.nombre','DESC')
									->get()->getResultArray();
		}
		foreach($products as $product){
			$responseProducts['products'][]=$product;
			$responseProducts['manufacturer'][$product['manufacturer_id']][]=$product['sku'];
			$responseProducts['categories'][$product['category_id']][]=$product['sku'];
			$responseProducts['partNumber'][$product['partNumber']][]=$product['sku'];			
		}
		return $responseProducts;
	}
}
