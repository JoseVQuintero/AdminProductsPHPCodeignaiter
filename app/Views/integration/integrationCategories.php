<?= $this->extend('layouts/main'); ?>
<?= $this->section('content'); ?>
<h1 class="h3 mb-3"><strong>Integration - Categories</strong></h1>
<div class="row">
    <div class="col-12 col-lg-12 col-xxl-12 d-flex">
        <div class="card flex-fill">
                <div class="card-header">
                    <h5 class="card-title mb-0">New Category <button id="saveCategory" class="btn btn-success btn-sm float-end btnAdd" data-bs-toggle="modal" data-bs-target="#formSaveModal">Save</button></h5>
                    <div id="alerts-segmentation" style="width:100%;"></div>
                </div>
                <div class="card-body">  
                    <select class="form-control categories-filter">  
                        <?php foreach ($Categories as $Category) : ?>                        
                            <optgroup label="<?=$Category['parent']["nombre"];?>">
                                <option value="<?=$Category['parent']["id"];?>"><?=$Category['parent']["nombre"];?></option>
                                <?php foreach ($Category['child'] as $Child) : ?>
                                    <option value="<?=$Child["id"];?>"><?=$Child["nombre"];?></option>
                                <?php endforeach; ?>
                            </optgroup>
                        <?php endforeach; ?>
                    </select>
                    <input type="text" class="form-control" id="add-category" name="add-category"/>                
                </div>            
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12 col-lg-12 col-xxl-12 d-flex">
        <div class="card flex-fill">            
            <div class="card-header">
                <h5 class="card-title mb-0">Products List <button class="btn btn-success btn-sm float-end btnRC">RC Save</button></h5>
                <div id="alerts-rc" style="width:100%;"></div>                
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
                            <th>ID</th>
                            <th>ID Shop</th>
                            <th>Nombre</th>
                            <th>Parent ID</th>
                            <th>Parent Group</th>
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

    function onCloseEdit(t,sku){
        $('.'+t+'-'+sku).show();
        $('#'+t+'container-'+sku).hide();
    }

    function sendAjax(t,data,sku){
        $.post("piTool/rc", {
            t: t,
            data: data,
            sku: sku
        }).done(function(data) {                
        });
    }

    function rc(type,id,sku){
        if(type=='title'){
            if($('#tcontainer-'+sku).html()==''){
                var input_title_ = document.createElement('input');
                input_title_.id = "rc-title-"+sku;
                input_title_.style = "width:90%";
                $('#tcontainer-'+sku).append(input_title_).append('<span style="margin:2px;"><a onclick=onCloseEdit(\'t\',\''+sku+'\')>X</a></span>');
                $("#rc-title-"+sku).val($('.t-'+sku).html())
                .on('keypress',function(e){
                    if(e.which== 13){
                        $('.t-'+sku).show();
                        $('.t-'+sku).html($("#rc-title-"+sku).val()); 
                        $('#tcontainer-'+sku).hide();
                        sendAjax('t',$("#rc-title-"+sku).val(),sku);
                    }
                });
            }
            $('.t-label').show();
            $('.tcontainer').hide();
            $('.t-'+sku).hide();
            $('#tcontainer-'+sku).show();
            $("#rc-title-"+sku).select2('open');
        }
        if(type=='manufacturer'){
            if($('#mcontainer-'+sku).html()==''){
                var select_manufacturer_ = select_manufacturer.cloneNode(true);
                select_manufacturer_.id = "rc-manufacturer-"+sku;
                select_manufacturer_.style = "width:90%";
                $('#mcontainer-'+sku).append(select_manufacturer_).append('<span style="margin:2px;"><a onclick=onCloseEdit(\'m\',\''+sku+'\')>X</a></span>');
                $("#rc-manufacturer-"+sku).select2({
                    width:'resolve'
                }).val(id).trigger('change')
                .on('select2:select',function(e){
                    $('.m-'+sku).show();
                    $('.m-'+sku).html(e.params.data.text); 
                    $('#mcontainer-'+sku).hide();
                    sendAjax('m',e.params.data.id,sku);
                });
			
            }
            $('.m-label').show();
            $('.mcontainer').hide();
            $('.m-'+sku).hide();
            $('#mcontainer-'+sku).show();
            $("#rc-manufacturer-"+sku).select2('open');
        }
        if(type=='category'){
            if($('#ccontainer-'+sku).html()==''){
                var select_category_ = select_category.cloneNode(true);
                select_category_.id = "rc-category-"+sku;
                select_category_.style = "width:90%";
                $('#ccontainer-'+sku).append(select_category_).append('<span style="margin:2px;"><a onclick=onCloseEdit(\'c\',\''+sku+'\')>X</a></span>');
                $("#rc-category-"+sku).select2().val(id).trigger('change')
                .on('select2:select',function(e){
                    $('.c-'+sku).show();
                    $('.c-'+sku).html(e.params.data.text); 
                    $('#ccontainer-'+sku).hide();
                    sendAjax('c',e.params.data.id,sku);
                });
            }
            $('.c-label').show();
            $('.ccontainer').hide();
            $('.c-'+sku).hide();
            $('#ccontainer-'+sku).show();
            $("#rc-category-"+sku).select2('open');
        }        
    }

    var select_manufacturer;
    var select_category;
    $(document).ready(function() {

        select_manufacturer = document.querySelector('.manufacturer-filter');
        select_category = document.querySelector('.categories-filter');        
        
        $('#formImagesModal').on('show.bs.modal', function(event){
            var button = $(event.relatedTarget);
            var recipient = button.data('html');           
            $('#image-view').html(recipient);
        })

        $('#formImagesViewModal').on('show.bs.modal', function(event){
            var button = $(event.relatedTarget);
            var recipient = button.data('image');     
            var imageDimension = document.createElement('img');
            imageDimension.src = recipient;
            $('#image-view-image').html('<p>Alto: '+imageDimension.height+', Ancho: '+imageDimension.width+'</p><img src="'+recipient+'"/>');
        })        

        $('#special-filter').select2(
            {
                tags: true,
                placeholder: "Special filter",
                allowClear: true,
                width:'100%'
                /*templateSelection: function(m){
                    var $m = $('<span>'+m.text+'</span>'+'<span><a href="#" onclick="buscar_products_manufacturer('+m.id+')"><img src="assets/img/icons/search.png"></a></span>')
                    return $m
                }*/
            }
        );
        
        $('#manufacturer').select2(
            {
                tags: true,
                placeholder: "Manufacturer filter",
                allowClear: true,
                width:'100%'
                /*templateSelection: function(m){
                    var $m = $('<span>'+m.text+'</span>'+'<span><a href="#" onclick="buscar_products_manufacturer('+m.id+')"><img src="assets/img/icons/search.png"></a></span>')
                    return $m
                }*/
            }
        );

        $('#categories').select2(
            {
                tags: true,
                placeholder: "Categories filter",
                allowClear: true,
                width:'100%'
                /*templateSelection: function(m){
                    var $m = $('<span>'+m.text+'</span>'+'<span><a href="#" onclick="buscar_products_categories('+m.id+')"><img src="assets/img/icons/search.png"></a></span>')
                    return $m
                }*/
            }
        );

        $('#linkerFindCategory').on('click', function(e) {         
            var text = '';   
            if(this.checked){
                text = 'Linker Category';
            }   
            tablerc.column([4]).search(text).draw();        
        });

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

        var groupColumn = 4;
        table = $('#products').DataTable({
            /* "processing":true,
            "serverSide": true, */
            //destroy: true,
            "autoWidth":true,
            "ajax": {
                "url": "getCategories",
                "type":"POST",
                "data": function(data){
                    data.country = $('#country-pitool').val()
                }
            },
            "columns": [
                    {
                        "data": "id"
                    },
                    {
                        "data": "shop_id"
                    },
                    {
                        "data": "nombre"
                    },
                    {
                        "data": "parent_id"
                    },
                    {
                        "data": "parent_group"
                    }
            ],
            "columnDefs": [
                    { 
                        targets: 0,
                        orderable: true,
                        visible: true, 
                    },
                    {
                        targets: [1],
                        orderable: true,
                        "visible": true
                    },
                    {
                        targets: [2],
                        orderable: true,
                        "visible": true,
                    },
                    {
                        targets: [3],
                        orderable: true,
                        "visible": true,
                    },
                    {
                        targets: [4],
                        orderable: true,
                        "visible": false,
                    }
            ],
            
            //"scrollX":true,
            "scrollY":"400px",
            initComplete: function(data) {

            },
            "order": [[ groupColumn, 'asc' ]],
            "displayLength": 10,
            "lengthMenu": [[10,25,50,100,500,-1],[10,25,50,100,500,"All"]],
            "drawCallback": function ( settings ) {
                var api = this.api();
                var rows = api.rows( {page:'current'} ).nodes();
                var last=null;
    
                api.column(groupColumn, {page:'current'} ).data().each( function ( group, i ) {
                    if ( last !== group ) {
                        $(rows).eq( i ).before(
                            '<tr class="group badge bg-success"><td width="100%" colspan="3">'+group+'</td></tr>'
                        );    
                        last = group;
                    }
                } );
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

        $('.manufacturer-filter').select2({
            tags: true,
            placeholder: "RC Manufacturer",
            allowClear: true,
            width:'100%'
        });
        $('.categories-filter').select2({
            tags: true,
            placeholder: "RC Categories",
            allowClear: true,
            width:'100%'
        });
        $('#country-pitool').select2({
            tags: true,
            placeholder: "Country",
            allowClear: true,
            width:'20%'
        });
        
        $('#check-all').on('click', function(e){
            $('#products .product-check').prop('checked',this.checked);
        });
    });
</script>
<?= $this->endSection(); ?>