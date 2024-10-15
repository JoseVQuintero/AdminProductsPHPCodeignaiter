<?= $this->extend('layouts/main'); ?>
<?= $this->section('content'); ?>

<h1 class="h3 mb-3"><strong>Integration</strong></h1>
<div class="row">
    <div class="col-12 col-lg-12 col-xxl-12 d-flex">
        <div class="card flex-fill">
                <div class="card-header">
                    <h5 class="card-title mb-0">Integration Products <button id="integrationFind" class="btn btn-success btn-sm float-end btnAdd" data-bs-toggle="modal" data-bs-target="#formSaveModal">Find</button></h5>
                    <div id="alerts-segmentation" style="width:100%;"></div>
                </div>
                <div class="card-body">         
                    <select width="100%" id="manufacturer" class="form-control select2" multiple="multiple">            
                        <?php foreach ($Manufacturers as $Manufacturer) : ?>
                            <?php $needHack = array_search($Manufacturer["id"],array_column($ManufacturerCount,'id'));?>
                            <?php $countManufacturer = ($needHack)?$ManufacturerCount[$needHack]['count']:0; ?>
                            <option value="<?=$Manufacturer["id"];?>"><?=$Manufacturer["manufacturer"]."(".$countManufacturer.")";?></option>
                        <?php endforeach; ?>
                    </select>
                
                    <button  class="btn btn-primary btn-sm float-end btnAddRole" data-bs-toggle="modal" data-bs-target="#formManufacturerModal">Block View Manufacturer</button>
                
                    <select width="100%" id="categories" class="form-control select2" multiple="multiple">     
                        <?php $arrayColumn = array_column($CategoryCount,'id');?>       
                        <?php foreach ($Categories as $Category) : ?>
                            <optgroup label="<?=$Category['parent']["nombre"];?>">
                                <?php $needHack = array_search($Category['parent']["id"],$arrayColumn);?>
                                <?php $countParentCategory = ($needHack)?$CategoryCount[$needHack]['countParent']:0; ?>
                                <option value="<?=$Category['parent']["id"];?>"><?=$Category['parent']["nombre"]." (All ".((count($Category['child']))?'+ '.count($Category['child']):'').")"."(".$countParentCategory.")";?></option>
                                <?php foreach ($Category['child'] as $Child) : ?>
                                    <?php $needHack_ = array_search($Child["id"],$arrayColumn);?>
                                    <?php $countParentCategory_ = ($needHack_)?$CategoryCount[$needHack_]['countSub']:0; ?>
                                    <option value="<?=$Child["id"];?>"><?=$Child["nombre"]."(".$countParentCategory_.")";?></option>
                                <?php endforeach; ?>
                            </optgroup>
                        <?php endforeach; ?>
                    </select>
                    <button  class="btn btn-primary btn-sm float-end btnAddRole" data-bs-toggle="modal" data-bs-target="#formCategoriesModal">Block View Categories</button>
                </div>            
        </div>
    </div>
</div>
<div class="row">
    <div class="col-12 col-lg-12 col-xxl-12 d-flex">
        <div class="card flex-fill">            
            <div class="card-header">
                <h5 class="card-title mb-0">Products List <!-- <button class="btn btn-success btn-sm float-end btnAdd" data-bs-toggle="modal" data-bs-target="#formUserModal">Exclude Product (Save)</button> --></h5>
                <div id="alerts-product" style="width:100%;"></div>
            </div>
            <div class="card-body">
                <table id="products" class="table table-hover my-0">
                    <!-- sku,
					if(title is null or title = \'\',concat(`INV-PRICE-DESC1-1-31`,`INV-PRICE-DESC2`),title) as title,
					m.manufacturer,
					c.category,
					p.`INV-STOCK` as stock,
					p.`INV-PRICE-RETAILS` as price_retails,
					p.`INV-CUST-COST` as price_cust_cost,
					if(ProductWidth is null or ProductWidth = \'\',`INV-WIDTH`,ProductWidth) as ProductWidth,
					if(ProductHeight is null or ProductHeight = \'\',`INV-HEIGHT`,ProductHeight) as ProductHeight,
					if(ProductWeight is null or ProductWeight = \'\',`INV-WEIGHT`,ProductWeight) as ProductWeight,
					if(ProductLength is null or ProductLength = \'\',`INV-LENGTH`,ProductLength) as ProductLength,
					image_product_heigth as images,
					ficha_json as null,
					ficha_html as null -->
                    <thead>
                        <tr>
                            <th></th>
                            <th>SKU</th>
                            <th>Title</th>
                            <th>Manufacturer</th>
                            <th>Category</th>
                            <th>Prices</th>
                            <th>Stock</th>
                            <th>Dimencions</th>
                            <th>Images</th>
                            <th>Shetees json</th>
                            <th>Shetees html</th>
                        </tr>
                    </thead>
                    <tbody>
                        
                    </tbody>
                </table>

            </div>
        </div>
    </div>
    <!-- <div class="col-12 col-lg-4 col-xxl-4 d-flex">
        <div class="card flex-fill">
            <div class="card-header">
                <h5 class="card-title mb-0">User Roles <button class="btn btn-primary btn-sm float-end btnAddRole" data-bs-toggle="modal" data-bs-target="#formRoleModal">Create New Role</button></h5>
            </div>
            <div class="card-body d-flex">
                <table class="table table-hover my-0">
                    <thead>
                        <tr>
                            <th>Role</th>
                            <th colspan="2"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php //foreach ($UserRole as $userRole) : ?>
                            <tr>
                                <td><?// =$userRole['role_name']; ?></td>
                                <td><a href="<?//= base_url('users/userRoleAccess?role=' . $userRole['id']); ?>"> <span class="badge bg-primary">Access Menu</span></a></td>
                                <td>
                                    <button class="btn btn-info btn-sm btnEditRole" data-bs-toggle="modal" data-bs-target="#formRoleModal" data-id="<?//= $userRole['id']; ?>" data-role="<?//= $userRole['role_name']; ?>">Update</button>
                                    <form action="<?//= base_url('users/deleteRole/' . $userRole['id']); ?>" method="post" class="d-inline">
                                        <input type="hidden" name="_method" value="DELETE">
                                        <button type="submit" class="btn btn-outline-danger btn-sm">
                                            Delete
                                        </button>
                                    </form>

                                </td>
                            </tr>
                        <?php //endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div> -->
</div>


<div class="modal fade" id="formManufacturerModal" tabindex="-1" aria-labelledby="formManufacturerModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="formUserModalLabel">Manufacturer</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="form-group mb-8">
                    <label for="" class="fw-bold">Segmentation Manufacturer</label>
                    <div class="input-group mt-8">
                        <?php foreach ($Manufacturers as $Manufacturer) : ?> 
                            <?php $needHack = array_search($Manufacturer["id"],array_column($ManufacturerCount,'id'));?>
                            <?php $countManufacturer = ($needHack)?$ManufacturerCount[$needHack]['count']:0; ?>               
                            <div class="form-check form-check-inline col-3 col-lg-3 col-xxl-3">
                                <input class="form-check-input manufacturerSeg" type="checkbox" id="manufacturer-block-<?=$Manufacturer["id"];?>" value="<?=$Manufacturer["id"];?>" >
                                <label style="font-size:10px;"class="form-check-label" for="manufacturer-block-<?=$Manufacturer["id"];?>"><?=$Manufacturer["manufacturer"]."(".$countManufacturer.")";?></label>
                            </div>        
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
<div class="modal fade" id="formCategoriesModal" tabindex="-1" aria-labelledby="formCategoriesModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="formUserModalLabel">Create New User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="form-group mb-8">
                    <label for="" class="fw-bold">Segmentation Categories</label>
                    <div class="input-group mt-8">
                        <?php foreach ($Categories as $Category) : ?>     
                            <?php $needHack = array_search($Category['parent']["id"],$arrayColumn);?>
                            <?php $countParentCategory = ($needHack)?$CategoryCount[$needHack]['countParent']:0; ?>           
                            <div class="form-check form-check-inline col-3 col-lg-3 col-xxl-3">
                                <input class="form-check-input categoriesSeg parent-<?=$Category['parent']["id"];?>" type="checkbox" id="categories-block-<?=$Category['parent']["id"];?>" value="<?=$Category['parent']["id"];?>" >
                                <label style="font-size:10px;" class="form-check-label" for="categorie-block-<?=$Category['parent']["id"];?>"><?=$Category['parent']["nombre"]."(".$countParentCategory.")";?></label>
                                <?php foreach ($Category['child'] as $Child) : ?>
                                    <?php $needHack_ = array_search($Child["id"],$arrayColumn);?>
                                    <?php $countParentCategory_ = ($needHack_)?$CategoryCount[$needHack_]['countSub']:0; ?>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input categoriesSeg subcategories-<?=$Category['parent']["id"];?>" type="checkbox" id="categories-block-<?=$Child["id"];?>" value="<?=$Child["id"];?>" >
                                        <label style="font-size:8px;"class="form-check-label" for="<?=$Child["id"];?>"><?=$Child["nombre"]."(".$countParentCategory_.")";?></label>
                                    </div>
                                <?php endforeach; ?>
                            </div>        
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="formImagesModal" tabindex="-1" aria-labelledby="formImagesModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="formUserModalLabel">View Images</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="image-view" class="form-group mb-8">
                    
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    var table;
    var filterManufacturer;
    var filterCategories;
    function refreshExcludeProduct(value){
        $.post("segmentation/segmentationProductExclude", {
            product: table.rows(value).data()[0].sku
        }).done(function(data) {  
            alerts("product",data)                   
        }); 
    }  

    function buscar_products_manufacturer(id){
        filterManufacturer= id;
        filterCategories= null;
        table.ajax.reload();
    }

    function buscar_products_categories(id){
        filterManufacturer= null;
        filterCategories= id;
        table.ajax.reload();
    }
    
    $(document).ready(function() {

        $('#formImagesModal').on('show.bs.modal', function(event){
            var button = $(event.relatedTarget);
            var recipient = button.data('html')           
            $('#image-view').html(recipient)
        })
        
        $('#manufacturer').select2(
            {
                tags: true,
                /*templateSelection: function(m){
                    var $m = $('<span>'+m.text+'</span>'+'<span><a href="#" onclick="buscar_products_manufacturer('+m.id+')"><img src="assets/img/icons/search.png"></a></span>')
                    return $m
                }*/
            }
        );

        $('#categories').select2(
            {
                tags: true,
                /*templateSelection: function(m){
                    var $m = $('<span>'+m.text+'</span>'+'<span><a href="#" onclick="buscar_products_categories('+m.id+')"><img src="assets/img/icons/search.png"></a></span>')
                    return $m
                }*/
            }
        );

        $('#manufacturer-filter').select2(
            {
                placeholder: "Manufacturer filter",
                allowClear: true
            }
        ).val('').trigger('change');

        $('#categories-filter').select2(
            {
                placeholder: "Categories filter",
                allowClear: true
            }
        ).val('').trigger('change');

        $('#integrationFind').on('click', function(e) {
            /*$.post("integration/integrationManufacturerCategories", {
                manufacturer: $('#manufacturer').val(),
                categories: $('#categories').val()
            }).done(function(data) {
                alerts("segmentation",data)                
            });*/
            filterManufacturer= $('#manufacturer').val();
            filterCategories= $('#categories').val();
            table.ajax.reload();
        })

        $('#manufacturer').on('select2:select', function(e) {
            var data = e.params.data;            
            $('#manufacturer-block-'+data.id).prop('checked',true);
        }).on('select2:unselect', function(e) {
            var data = e.params.data;           
            $('#manufacturer-block-'+data.id).prop('checked',false);
        });    

        $(".manufacturerSeg").on('change',function(e){
            if($(this).prop('checked')){
                var mValue = $('#manufacturer').val();
                mValue.push(this.value);
                $('#manufacturer').val(mValue).trigger('change');                
            }else{
                var mValue = $('#manufacturer').val();
                mValue.splice(mValue.indexOf(this.value), 1);
                $('#manufacturer').val(mValue).trigger('change');
            }            
        });

        $('#categories').on('select2:select', function(e) {
            var data = e.params.data;            
            if($('#categories-block-'+data.id).hasClass("parent-"+data.id)){     
                var mValue = $(this).val();           
                $('.subcategories-'+data.id).prop('checked',true);
                $('.subcategories-'+data.id).each(function(index,elem){
                    mValue.push(elem.value);
                });
                $('#categories').val(mValue).trigger('change');
            }
            $('#categories-block-'+data.id).prop('checked',true);
        }).on('select2:unselect', function(e) {
            var data = e.params.data;
            if($('#categories-block-'+data.id).hasClass("parent-"+data.id)){
                var mValue = $(this).val();
                $('.subcategories-'+data.id).prop('checked',false);
                $('.subcategories-'+data.id).each(function(index,elem){
                    mValue.splice(mValue.indexOf(elem.value), 1);
                });
                $('#categories').val(mValue).trigger('change');
            }
            $('#categories-block-'+data.id).prop('checked',false);
        });    

        $(".categoriesSeg").on('change',function(e){
            if($(this).prop('checked')){
                var mValue = $('#categories').val();
                if($('#categories-block-'+this.value).hasClass("parent-"+this.value)){
                    $('.subcategories-'+this.value).prop('checked',true);
                    $('.subcategories-'+this.value).each(function(index,elem){
                        mValue.push(elem.value);
                    });
                }                
                mValue.push(this.value);
                $('#categories').val(mValue).trigger('change');
            }else{
                var mValue = $('#categories').val();
                if($('#categories-block-'+this.value).hasClass("parent-"+this.value)){
                    $('.subcategories-'+this.value).prop('checked',false);
                    $('.subcategories-'+this.value).each(function(index,elem){
                        mValue.splice(mValue.indexOf(elem.value), 1);
                    });
                }                
                mValue.splice(mValue.indexOf(this.value), 1);
                $('#categories').val(mValue).trigger('change');
            }            
        });        
        var arrayExclude = JSON.parse('<?=$ProductsSeg; ?>');
        var groupColumn = 3;
        table = $('#products').DataTable({
            /* "processing":true,
            "serverSide": true, */
            //destroy: true,
            "ajax": {
                "url": "integration/getProducts",
                "type":"POST",
                "data": function(data){
                    data.manufacturer = filterManufacturer,
                    data.categories = filterCategories
                }
            },
            "columns": [
                    {
                        "data": ""
                    },
                    {
                        "data": "sku"
                    },
                    {
                        "data": "title"
                    },
                    {
                        "data": "manufacturer"
                    },
                    {
                        "data": "category"
                    },
                    {
                        "data": "prices"
                    },
                    {
                        "data": "stock"
                    },
                    {
                        "data": "dimension"
                    },
                    {
                        "data": "images"
                    },
                    {
                        "data": "ficha_json"
                    },
                    {
                        "data": "ficha_html"
                    }
            ],
            "scrollX":true,
            "columnDefs": [
                    { 
                        targets: 0,
                        orderable: false,
                        visible: true, 
                        className: 'dt-body-center',                    
                        'render': function (data,type,full,meta){
                            var checked = (arrayExclude.indexOf(full.sku)>=0)?'':'checked';                            
                            return '<input type="checkbox" name="id[]" onclick="refreshExcludeProduct('+meta.row+')" class="product-check" value="'+meta.row+'" '+checked+'>';
                        }
                    },
                    {
                        targets: [1],
                        "visible": true
                    },
                    {
                        targets: [2],
                        "visible": true
                        
                    },
                    {
                        targets: [3],
                        "visible": true,
                        'render': function (data,type,full,meta){
                            return '<span class="badge bg-success">'+full.manufacturer+'</span>';
                        }
                    },
                    {
                        targets: [4],
                        "visible": true,
                        'render': function (data,type,full,meta){
                            return '<span class="badge bg-success">'+full.category+'</span>';
                        }
                    },
                    {
                        targets: [5],
                        "visible": true
                    },
                    {
                        targets: [6],
                        "visible": true
                    },
                    {
                        targets: [7],
                        "visible": true
                    },
                    {
                        targets: [8],
                        orderable: false,
                        visible: true, 
                        className: '',                    
                        'render': function (data,type,full,meta){                         
                            var div = '';
                            if(full.images){
                                div = '<div class="input-group mt-8">';
                                JSON.parse(full.images).forEach(item => {
                                    div +='<div class="form-check form-check-inline col-3 col-lg-3 col-xxl-3">';
                                    div +='<img id="images-'+full.sku+'" src="'+item+'" style="width:48px;height:48px;">';
                                    div +='</div>';   
                                })   
                                div += '</div>';  
                            }             
                            return '<button class="btn btn-info btn-sm btnEdit" data-bs-toggle="modal" data-bs-target="#formImagesModal" data-html=\''+div+'\'>View</button>';
                        }
                    },
                    {
                        targets: [9],
                        orderable: false,
                        visible: true, 
                        className: '',                    
                        'render': function (data,type,full,meta){                         
                            return '<button class="btn btn-info btn-sm btnEdit" data-bs-toggle="modal" data-bs-target="#formHtmlModal" data-html="'+full.ficha_html+'">View</button>';
                        }
                    },
                    {
                        targets: [10],
                        orderable: false,
                        visible: true, 
                        className: '',                    
                        'render': function (data,type,full,meta){                         
                            return '<button class="btn btn-info btn-sm btnEdit" data-bs-toggle="modal" data-bs-target="#formJsonModal" data-html="'+full.ficha_json+'">View</button>';
                        }
                    }
            ],
            select:{
                style: 'os',
                selector: 'td:first-child'
            },

            initComplete: function(data) {

            },
            "order": [[ groupColumn, 'asc' ]],
            "displayLength": 5,
            "drawCallback": function ( settings ) {
                var api = this.api();
                var rows = api.rows( {page:'current'} ).nodes();
                var last=null;
    
                api.column(groupColumn, {page:'current'} ).data().each( function ( group, i ) {
                    if ( last !== group ) {
                        $(rows).eq( i ).before(
                            '<tr class="group badge bg-success"><td width="100%" colspan="11">'+group+'</td></tr>'
                        );    
                        last = group;
                    }
                } );
            }
        } );
    
        // Order by the grouping
        $('#products tbody').on( 'click', 'tr.group', function () {
            var currentOrder = table.order()[0];
            if ( currentOrder[0] === groupColumn && currentOrder[1] === 'asc' ) {
                table.order( [ groupColumn, 'desc' ] ).draw();
            }
            else {
                table.order( [ groupColumn, 'asc' ] ).draw();
            }
        });

        $('#manufacturer').val(JSON.parse('<?=$ManufacturerSeg; ?>')).trigger('change');
        $('#categories').val(JSON.parse('<?=$CategorySeg; ?>')).trigger('change');

        JSON.parse('<?=$ManufacturerSeg; ?>').forEach(item => {
            $('#manufacturer-block-'+item).prop('checked',true);
        });

        JSON.parse('<?=$CategorySeg; ?>').forEach(item => {
            $('#categories-block-'+item).prop('checked',true);
        });

        $('.filter-product').on('change',function(e){           
            filterManufacturer= $('#manufacturer-filter').val();
            filterCategories= $('#categories-filter').val();
            table.ajax.reload();
        });

    });
</script>
<?= $this->endSection(); ?>