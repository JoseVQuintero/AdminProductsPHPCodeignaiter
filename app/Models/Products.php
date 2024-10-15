<?php

namespace App\Models;

use CodeIgniter\Model;

class Products extends Model
{
	
	public function setRC($country=null,$Manufacturer=null,$Categories=null,$type=null){
		if($type=='rccategory'){
			$filter =  (!is_null($Categories))?" and c.id_categoria_ingram in (".implode(",",$Categories).")":'';
			$this->db->query("update 
									products_".$country." p, categories_".$country." c 
							  set p.type_rc = 1, p.updated_at_rc = '".returnDate(returnAccess()['zone'][$country])."', p.id_category = c.id_categoria_ingram 
							  where p.id_subcategoria=c.id".$filter);
		}

		if($type=='rcmanufacturer'){
			$filter =  (!is_null($Manufacturer))?" and m.id in (".implode(",",$Manufacturer).")":'';
			$this->db->query("update 
									products_".$country." p, manufacturer_".$country." m 
							  set p.type_rc = 1, p.updated_at_rc = '".returnDate(returnAccess()['zone'][$country])."', p.id_manufacturer = if(m.linker_id is null,m.id,m.linker_id) 
							  where p.id_marca=if(m.linker_id is null,m.id,m.linker_id)".$filter);
		}

		if($type=='rcmanufactureringram'){
			$filter =  (!is_null($Manufacturer))?" and m.id in (".implode(",",$Manufacturer).")":'';
			$this->db->query("update 
									products_".$country." p, manufacturer_".$country." m 
							  set p.type_rc = 1, p.updated_at_rc = '".returnDate(returnAccess()['zone'][$country])."', p.id_manufacturer = if(m.linker_id_marca_ingram is null,m.id_marca_ingram,m.linker_id_marca_ingram) 
							  where p.id_marca=if(m.linker_id_marca_ingram is null,m.id_marca_ingram,m.linker_id_marca_ingram)".$filter);
		}
	}
	
	public function getManufacturerPiTool($country){
		return $this->db->table('manufacturer_'.$country.' as m')
				->select('
					id,
					manufacturer as name,
					id_marca_ingram as ingram_id,
					linker_id,
					linker_id_marca_ingram as union_ingram_id,
					created_at
				')
				->orderBy('m.manufacturer','ASC')
				->get()->getResultArray();	
	}
	public function getManufacturer($country){
		return $this->db->table('manufacturer_'.$country.' as m')
				->select('id,manufacturer')
				->orderBy('m.manufacturer','ASC')
				->get()->getResultArray();	
	}
	public function getCategories($country){
		$categories =$this->db->table('categorias_'.$country.' as c')
							->select('id,parent_id,nombre')
							->where(["parent_id"=>0])
							->orderBy('c.nombre','ASC')
							->get()->getResultArray();	
		$listCategories = [];
		foreach($categories as $category){
			$categories_chield = $this->db->table('categorias_'.$country.' as c')
														->select('id,parent_id,nombre')
														->where(["parent_id"=>$category["id"]])
														->orderBy('c.nombre','ASC')
														->get()->getResultArray();
			$listCategories[] = ["parent"=>$category,"child"=>$categories_chield];
		}
		return $listCategories;
	}
	public function getCategoriesIngram($country){
		$categories =$this->db->table('categories_'.$country.' as c')
							->select('c.id,c.parent_id,concat(category,\' ( \',if(cc.nombre<>\'\',cc.nombre,\'Not Asiggne\'),\' ) \') as category')
							->join('categorias_'.$country.' as cc', 'c.id_categoria_ingram=cc.id','left')
							->where(["c.parent_id"=>0,"c.type"=>"category-product"])
							->orderBy('c.category','ASC')
							->get()->getResultArray();	
		$listCategories = [];
		foreach($categories as $category){
			$categories_chield = $this->db->table('categories_'.$country.' as c')
														->select('c.id,c.parent_id,concat(category,\' ( \',if(cc.nombre<>\'\',cc.nombre,\'Not Asiggne\'),\' ) \') as category')
														->join('categorias_'.$country.' as cc', 'c.id_categoria_ingram=cc.id','left')
														->where(["c.parent_id"=>$category["id"],"c.type"=>"sub-product"])
														->orderBy('c.category','ASC')
														->get()->getResultArray();
			$listCategories[] = ["parent"=>$category,"child"=>$categories_chield];
		}
		return $listCategories;
	}
	public function getCategoriesPiToolRC($country){
		//$filter = "c.parent_id = 0 and c.type = 'category-product'";
		$categories =$this->db->table('categories_'.$country.' as c')
							->select('c.id as ingram_id,c.category as ingram_name,c.parent_id as ingram_parent_id,cc.id as id,if(cc.nombre<>\'\',cc.nombre,\'\') as name,cc.parent_id as parent_id')
							->join('categorias_'.$country.' as cc', 'c.id_categoria_ingram=cc.id','left')
							->where(["c.parent_id"=>0,"c.type"=>"category-product"])
							//->where($filter)
							->orderBy('c.category','ASC')
							->get()->getResultArray();	
		$listCategories = [];
		foreach($categories as $category){
			$category['parent_group']=$category['ingram_name'];
			$listCategories[] = $category;
			//$filter = "c.parent_id = ".$category["ingram_id"]." and c.type = 'sub-product'";
			$categories_chield = $this->db->table('categories_'.$country.' as c')
														->select('c.id as ingram_id,c.category as ingram_name,c.parent_id as ingram_parent_id,cc.id as id,if(cc.nombre<>\'\',cc.nombre,\'\') as name,cc.parent_id as parent_id')
														->join('categorias_'.$country.' as cc', 'c.id_categoria_ingram=cc.id','left')
														->where(["c.parent_id"=>$category["ingram_id"],"c.type"=>"sub-product"])
														//->where($filter)
														->orderBy('c.category','ASC')
														->get()->getResultArray();
			$categories_chield_=[];										
			foreach($categories_chield as $chield){
				$chield['parent_group']=$category['ingram_name'];
				$categories_chield_[]=$chield;
			}
			$listCategories = array_merge($listCategories,$categories_chield_);
		}
		return $listCategories;
	}
	public function getCategoriesPiTool($country){
		$categories =$this->db->table('categorias_'.$country.' as c')
							->select('null as parent_group,id,parent_id,nombre')
							->where(["parent_id"=>0])
							->orderBy('c.nombre','ASC')
							->get()->getResultArray();	
		$listCategories = [];
		foreach($categories as $category){
			$category['parent_group']=$category['nombre'];
			$listCategories[] = $category;
			$categories_chield = $this->db->table('categorias_'.$country.' as c')
														->select('null as parent_group, id,parent_id,nombre')
														->where(["parent_id"=>$category["id"]])
														->orderBy('c.nombre','ASC')
														->get()->getResultArray();	
			$categories_chield_=[];										
			foreach($categories_chield as $chield){
				$chield['parent_group']=$category['nombre'];
				$categories_chield_[]=$chield;
			}
			$listCategories = array_merge($listCategories,$categories_chield_);
		}
		return $listCategories;
	}
	public function countManufacturer($country,$Manufacturer,$ProductExclude){
		return 
				$this->db->table('products_'.$country.' as p')
				->select('count(*) as count,m.id')				
				->join('manufacturer_'.$country.' as m', 'p.id_marca = m.id')
				//->where('p.id_marca in (\''.implode("','",$Manufacturer).'\') and p.sku not in (\''.implode("','",$ProductExclude).'\')')
				->groupBy('p.id_marca')
				->get()->getResultArray();	
	}
	public function countCategories($country,$Categories,$ProductExclude){
		return 
				$this->db->table('products_'.$country.' as p')
				->select('
							sum(if(ci.parent_id = 0,1,0)) as countParent,
							sum(if(ci.parent_id > 0,1,0)) as countSub,
							ci.id
						')
				//->join('categories_'.$country.' as c', 'p.id_subcategoria = c.id','left')
				->join('categorias_'.$country.' as ci', 'ci.id = p.id_category','left')
				//->where('c.type in (\'category-product\',\'sub-product\')')
				//->where('ci.id in (\''.implode("','",$Categories).'\') and p.sku not in (\''.implode("','",$ProductExclude).'\')')
				->groupBy('ci.id')
				->get()->getResultArray();
	}
	public function getProducts($country = '', $manufacturer = null, $category = null)
	{	
		if($manufacturer){$filter['p.id_marca'] = $manufacturer;}
		if($category){$filter['ci.id'] = $category;}

		if(!isset($filter)){$filter='((DATEDIFF( now(),date_add)) <= 100 or (DATEDIFF( now(),updated)) <= 120)';}
		$select='
					sku,
					if(title is null or title = \'\',concat(`INV-PRICE-DESC1-1-31`,`INV-PRICE-DESC2`),title) as title,
					m.manufacturer,
					ci.nombre as category,
					p.`INV-STOCK` as stock,
					concat_ws("",\'P.R: \',p.`INV-PRICE-RETAIL`,\'<br>P.C: \',p.`INV-CUST-COST`) as prices,
					concat_ws("",
						\'Wid: \', if(ProductWidth is null or ProductWidth = \'\',`INV-WIDTH`,ProductWidth),
						\'Hei: \', if(ProductHeight is null or ProductHeight = \'\',`INV-HEIGHT`,ProductHeight), \'<br>\'
						\'Wei: \', if(ProductWeight is null or ProductWeight = \'\',`INV-WEIGHT`,ProductWeight),
						\'Len: \', if(ProductLength is null or ProductLength = \'\',`INV-LENGTH`,ProductLength)
					) as dimension,
					image_product_heigth as images,
					null as ficha_json,
					null as ficha_html,
					m.id as manufacturer_id,
					ci.id as category_id
		';	
		return 
				$this->db->table('products_'.$country.' as p')
				->select($select)
				/*->join('categories_'.$country.' as c', 'p.id_categoria = c.id')
				->join('categorias_'.$country.' as ci', 'ci.id = c.id_categoria_ingram')*/
				//->join('categories_'.$country.' as c', 'p.id_categoria = c.id')
				->join('categorias_'.$country.' as ci', 'ci.id = p.id_category')
				//->join('manufacturer_'.$country.' as m', 'p.id_marca = m.id')
				->join('manufacturer_'.$country.' as m', 'p.id_manufacturer = m.id')
				->where($filter)
				->orderBy('m.manufacturer','DESC')
				->orderBy('ci.nombre','DESC')
				->get()->getResultArray();		
	}

	public function getAccessMenuCategory($role)
	{
		return $this->db->table('user_menu_category')
			->select('*,user_menu_category.id AS menuCategoryID')
			->join('user_access', 'user_menu_category.id = user_access.menu_category_id')
			->where(['user_access.role_id' => $role])
			->get()->getResultArray();
	}
	public function getAccessMenu($role)
	{
		return $this->db->table('user_menu')
			->join('user_access', 'user_menu.id = user_access.menu_id')
			->where(['user_access.role_id' => $role])
			->get()->getResultArray();
	}

	public function getMenuCategory()
	{
		return $this->db->table('user_menu_category')
			->get()->getResultArray();
	}
	public function getMenu()
	{
		return $this->db->table('user_menu')
			->get()->getResultArray();
	}

	public function getSubmenu()
	{
		return $this->db->table('user_submenu')
			->get()->getResultArray();
	}
	public function getUserRole($role = false)
	{
		if ($role) {
			return $this->db->table('user_role')
				->where(['id' => $role])
				->get()->getRowArray();
		}

		return $this->db->table('user_role')
			->get()->getResultArray();
	}

	public function createMenuCategory($dataMenuCategory)
	{
		return $this->db->table('user_menu_category')->insert([
			'menu_category'		=> $dataMenuCategory['inputMenuCategory']
		]);
	}
	public function createMenu($dataMenu)
	{
		return $this->db->table('user_menu')->insert([
			'menu_category'	=> $dataMenu['inputMenuCategory'],
			'title'			=> $dataMenu['inputMenuTitle'],
			'url' 			=> $dataMenu['inputMenuURL'],
			'icon' 			=> $dataMenu['inputMenuIcon'],
			'parent' 		=> 0
		]);
	}

	public function createSubMenu($dataSubmenu)
	{
		$this->db->transBegin();
		$this->db->table('user_submenu')->insert([
			'menu'			=> $dataSubmenu['inputMenu'],
			'title'			=> $dataSubmenu['inputSubmenuTitle'],
			'url' 			=> $dataSubmenu['inputSubmenuURL']
		]);
		$this->db->table('user_menu')->update(['parent' => 1], ['id' => $dataSubmenu['inputMenu']]);
		if ($this->db->transStatus() === FALSE) {
			$this->db->transRollback();
			return false;
		} else {
			$this->db->transCommit();
			return true;
		}
	}

	public function getMenuByUrl($menuUrl)
	{
		return $this->db->table('user_menu')
			->where(['url' => $menuUrl])
			->get()->getRowArray();
	}
	public function createUser($dataUser)
	{
		return $this->db->table('users')->insert([
			'fullname'		=> $dataUser['inputFullname'],
			'username' 		=> $dataUser['inputUsername'],
			'password' 		=> password_hash($dataUser['inputPassword'], PASSWORD_DEFAULT),
			'role' 			=> $dataUser['inputRole'],
			'created_at'    => date('Y-m-d h:i:s')
		]);
	}
	public function updateUser($dataUser)
	{
		if ($dataUser['inputPassword']) {
			$password = password_hash($dataUser['inputPassword'], PASSWORD_DEFAULT);
		} else {
			$user 		= $this->getUser($dataUser['userID']);
			$password 	= $user['password'];
		}
		return $this->db->table('users')->update([
			'fullname'		=> $dataUser['inputFullname'],
			'username' 		=> $dataUser['inputUsername'],
			'password' 		=> $password,
			'role' 			=> $dataUser['inputRole'],
		], ['id' => $dataUser['userID']]);
	}
	public function deleteUser($userID)
	{
		return $this->db->table('users')->delete(['id' => $userID]);
	}

	public function createRole($dataRole)
	{
		return $this->db->table('user_role')->insert(['role_name' => $dataRole['inputRoleName']]);
	}
	public function updateRole($dataRole)
	{
		return $this->db->table('user_role')->update(['role_name' => $dataRole['inputRoleName']], ['id' => $dataRole['roleID']]);
	}
	public function deleteRole($role)
	{
		return $this->db->table('user_role')->delete(['id' => $role]);
	}
	public function checkUserMenuCategoryAccess($dataAccess)
	{
		return  $this->db->table('user_access')
			->where([
				'role_id' => $dataAccess['roleID'],
				'menu_category_id' => $dataAccess['menuCategoryID']
			])
			->countAllResults();
	}

	public function checkUserAccess($dataAccess)
	{
		return  $this->db->table('user_access')
			->where([
				'role_id' => $dataAccess['roleID'],
				'menu_id' => $dataAccess['menuID']
			])
			->countAllResults();
	}

	public function checkUserSubmenuAccess($dataAccess)
	{
		return  $this->db->table('user_access')
			->where([
				'role_id' => $dataAccess['roleID'],
				'submenu_id' => $dataAccess['submenuID']
			])
			->countAllResults();
	}
	public function insertMenuCategoryPermission($dataAccess)
	{
		return $this->db->table('user_access')->insert(['role_id' => $dataAccess['roleID'], 'menu_category_id' => $dataAccess['menuCategoryID']]);
	}
	public function deleteMenuCategoryPermission($dataAccess)
	{
		return $this->db->table('user_access')->delete(['role_id' => $dataAccess['roleID'], 'menu_category_id' => $dataAccess['menuCategoryID']]);
	}

	public function insertMenuPermission($dataAccess)
	{
		return $this->db->table('user_access')->insert(['role_id' => $dataAccess['roleID'], 'menu_id' => $dataAccess['menuID']]);
	}
	public function deleteMenuPermission($dataAccess)
	{
		return $this->db->table('user_access')->delete(['role_id' => $dataAccess['roleID'], 'menu_id' => $dataAccess['menuID']]);
	}

	public function insertSubmenuPermission($dataAccess)
	{
		return $this->db->table('user_access')->insert(['role_id' => $dataAccess['roleID'], 'submenu_id' => $dataAccess['submenuID']]);
	}
	public function deleteSubmenuPermission($dataAccess)
	{
		return $this->db->table('user_access')->delete(['role_id' => $dataAccess['roleID'], 'submenu_id' => $dataAccess['submenuID']]);
	}
}
