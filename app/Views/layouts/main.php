<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<meta name="description" content="Responsive Admin &amp; Dashboard Template based on Bootstrap 5">
	<meta name="author" content="Gheav">
	<meta name="keywords" content="Gheav, bootstrap, bootstrap 5, admin, dashboard, template, responsive, css, sass, html, theme, front-end, ui kit, web">

	<link rel="shortcut icon" href="img/icons/icon-48x48.png" />

	<title>Admin - Shop Integrate</title>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-F3w7mX95PdgyTmZZMECAngseQB83DfGTowi0iMjiWaeVhAn4FJkqJByhZMI3AhiU" crossorigin="anonymous">
	<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&display=swap" rel="stylesheet">
	<link href="<?= base_url('assets/css/app.css') ?>" rel="stylesheet">
	<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&display=swap" rel="stylesheet">
	<link rel="stylesheet" href="<?= base_url('assets/css/datatables.css') ?>"/>
	<link rel="stylesheet" href="<?= base_url('assets/js/select2/css/select2.min.css') ?>">	
	<link rel="stylesheet" href="<?= base_url('assets/css/sweetalert2.css') ?>">	

	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script> 	
	<script src="<?= base_url('assets/js/app.js') ?>"></script>
	<script src="<?= base_url('assets/js/datatables.js') ?>"></script>
	<script src="<?= base_url('assets/js/select2/js/select2.full.min.js') ?>"></script>
	<script src="<?= base_url('assets/js/sweetalert2.js') ?>"></script>
    <style>
		table.dataTable td {
			font-size: 8pt;
		}
		/* .select2-container.select2-dropdown-open{
			width:170%;
			margin-left:-70%;
		} */
	</style>
</head>

<body>
	<div class="wrapper">
		<?= $this->include('layouts/sidebar'); ?>
		<div class="main">
			<!-- HEADER: MENU + HEROE SECTION -->
			<?= $this->include('layouts/header'); ?>
			<!-- CONTENT -->
			<main class="content">
				<div class="container-fluid p-0">
					<?= $this->include('common/alerts'); ?>
					<?= $this->renderSection('content'); ?>
				</div>
			</main>
			<!-- FOOTER: DEBUG INFO + COPYRIGHTS -->
			<?= $this->include('layouts/footer'); ?>
		</div>
	</div>
	<script>
		function alerts(selector,obj){
			$('#alerts-'+selector).html(  '<div class="alert alert-'+obj[0]+' alert-dismissible" role="alert">'+
						'<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>'+
						'<div class="alert-icon">'+
							'<i class="align-middle" data-feather="alert-circle"></i>'+
						'</div>'+
						'<div class="alert-message">'+obj[2]+
						'</div>'+
					'</div>');
			$('#alerts-'+selector).show();
			setTimeout(function(){
				$('#alerts-'+selector).hide();
			},2000);
		}
		function ajaxSwal(obj){
			Swal.fire({				
				title: obj.title,//'Are you sure?',
				text: obj.text,//"You won't be able to revert this!",
				icon: obj.icon,//'warning',
				showCancelButton: true,
				confirmButtonColor: '#3085d6',
				cancelButtonColor: '#d33',
				showLoaderOnConfirm: true,
				confirmButtonText: obj.confirmButtonText,//'Yes, delete it!'
				preConfirm: () => {					
					//return obj.function(obj.param.type,functionOk);
					return fetch(obj.url/*'piTool/setRC'*/,{
						method:'POST',
						body: JSON.stringify(obj.data)
					})
					.then(response => {
						if (!response.ok) {
						throw new Error(response.statusText)
						}
						return response.json()
					})
					.catch(error => {
						Swal.showValidationMessage(
						`Request failed: ${error}`
						)
					})
				},
				allowOutsideClick: () => !Swal.isLoading(),
				}).then((result) => {
				if (result.isConfirmed) {	
					Swal.fire(
						obj.actionText,//'Deleted!',
						obj.messageText,//'Your file has been deleted.',
						'success'
					)
				}
			});
		}
	</script>
</body>

</html>