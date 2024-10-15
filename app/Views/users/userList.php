<?= $this->extend('layouts/main'); ?>
<?= $this->section('content'); ?>
<h1 class="h3 mb-3"><strong>Users</strong> Menu </h1>
<div class="row">
    <div class="col-12 col-lg-8 col-xxl-8 d-flex">
        <div class="card flex-fill">
            <div class="card-header">
                <h5 class="card-title mb-0">Users List <button class="btn btn-primary btn-sm float-end btnAdd" data-bs-toggle="modal" data-bs-target="#formUserModal">Create New User</button></h5>
            </div>
            <div class="card-body">
                <table class="table table-hover my-0">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th class="d-none d-xl-table-cell">Username</th>
                            <th>Role</th>
                            <th>Created at</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php

use Kint\Zval\Value;
echo "<script>access=[];</script>";
 foreach ($Users as $users) : ?>
                            <tr>
                                <td><?= $users['fullname']; ?></td>
                                <td class="d-none d-md-table-cell"><?= $users['username']; ?></td>
                                <td><span class="badge bg-success"><?= $users['role_name']; ?></span></td>
                                <td><?= $users['created_at']; ?></td>
                                <td>
                                    <button class="btn btn-info btn-sm btnEdit" data-bs-toggle="modal" data-bs-target="#formUserModal" 
                                    data-id="<?= $users['userID']; ?>" 
                                    data-fullname="<?= $users['fullname']; ?>" 
                                    data-username="<?= $users['username']; ?>" 
                                    data-role="<?= $users['role']; ?>">
                                    Update
                                    </button>
                                    <?php echo "<script>access[".$users['userID']."]=JSON.parse('".((empty($users['access']))?json_encode($Access):$users['access'])."')</script>";?>
                                    <?php if ($users['username'] != session()->get('username')) : ?>
                                        <form action="<?= base_url('users/deleteUser/' . $users['userID']); ?>" method="post" class="d-inline">
                                            <input type="hidden" name="_method" value="DELETE">
                                            <button class="btn btn-danger btn-sm" onclick="return confirm('Are you sure delete <?= $users['username']; ?> ?')">Delete</button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-12 col-lg-4 col-xxl-4 d-flex">
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
                        <?php foreach ($UserRole as $userRole) : ?>
                            <tr>
                                <td><?= $userRole['role_name']; ?></td>
                                <td><a href="<?= base_url('users/userRoleAccess?role=' . $userRole['id']); ?>"> <span class="badge bg-primary">Access Menu</span></a></td>
                                <td>
                                    <button class="btn btn-info btn-sm btnEditRole" data-bs-toggle="modal" data-bs-target="#formRoleModal" data-id="<?= $userRole['id']; ?>" data-role="<?= $userRole['role_name']; ?>">Update</button>
                                    <form action="<?= base_url('users/deleteRole/' . $userRole['id']); ?>" method="post" class="d-inline">
                                        <input type="hidden" name="_method" value="DELETE">
                                        <button type="submit" class="btn btn-outline-danger btn-sm">
                                            Delete
                                        </button>
                                    </form>

                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>


<div class="modal fade" id="formUserModal" tabindex="-1" aria-labelledby="formUserModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="formUserModalLabel">Create New User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= base_url('users/createUser'); ?>" method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" name="userID" id="userID">
                    <div class="mb-3">
                        <label for="inputFullname" class="col-form-label">Full Name:</label>
                        <input type="text" class="form-control" name="inputFullname" id="inputFullname" required>
                        <input type="file" class="form-control" name="avatar" id="avatar">
                    </div>
                    <div class="mb-3">
                        <label for="inputUsername" class="col-form-label">Username:</label>
                        <input type="text" class="form-control" name="inputUsername" id="inputUsername" required>
                    </div>
                    <div class="mb-3">
                        <label for="inputPassword" class="col-form-label">Password:</label>
                        <input type="password" class="form-control" name="inputPassword" id="inputPassword" required>
                    </div>
                    <div class="mb-3">
                        <label for="inputRole" class="col-form-label">Role:</label>
                        <select name="inputRole" id="inputRole" class="form-control" required>
                            <option value="">-- Choose User Role --</option>
                            <?php foreach ($UserRole as $userRole) : ?>
                                <option value="<?= $userRole['id']; ?>"><?= $userRole['role_name']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="shopdefault" class="col-form-label">Zone Access:</label>
                        <?php $access_ = $Access;?>
                        <select name="access[zonedefault]" id="zonedefault" class="form-control" required>
                            <option value="">-- Choose Zone Access --</option>
                            <?php foreach ($access_['zone'] as $key=>$value) : ?>
                                <option value="<?= $key; ?>" <?=($key==$access_['zonedefault'])?'selected':'';?>><?= $key." - ".$value; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="default" class="col-form-label">Type Access:</label>
                        <?php $access_ = $Access;?>
                        <select name="access[default]" id="default" class="form-control" required>
                            <option value="">-- Choose Type Access --</option>
                            <?php foreach ($access_ as $key=>$value) : if($key!='default'&&$key!='shopdefault'&&$key!='shop'){?>
                                <option value="<?= $key; ?>" <?=($key==$access_['default'])?'selected':'';?>><?= $key; ?></option>
                            <?php }endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="inputRole" class="col-form-label">Access Sync:</label>    
                        <?php  foreach ($access_ as $key_=>$value_) : if(!in_array($key_,['zonedefault','zone','default','shopdefault','shop'])){?>                   
                        <?php  foreach ($value_ as $key=>$value) : ?>
                            <div class="mb-3 <?=$key_;?>-visibility access" <?=($key_==$access_['default'])?'':'style="display:none"';?>>
                                <label for="inputPassword" class="col-form-label"><?=$key;?>:</label>
                                <input type="text" class="form-control access-value"  name="access[<?=$key_;?>][<?=$key;?>]" id="input-<?=$key_;?>-<?=$key;?>" value="">
                            </div>
                        <?php endforeach; }endforeach; ?>
                    </div>

                    <div class="mb-3">
                        <label for="shopdefault" class="col-form-label">Shop Type Access:</label>
                        <?php $access_ = $Access;?>
                        <select name="access[shopdefault]" id="shopdefault" class="form-control" required>
                            <option value="">-- Choose Type Access --</option>
                            <?php foreach ($access_['shop'] as $key=>$value) : ?>
                                <option value="<?= $key; ?>" <?=($key==$access_['shopdefault'])?'selected':'';?>><?= $key; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="inputRole" class="col-form-label">Shop Access Sync:</label>    
                        <?php  foreach ($access_['shop'] as $key_=>$value_) :?>                   
                        <?php  foreach ($value_ as $key=>$value) : ?>
                            <div class="mb-3 shop-<?=$key_;?>-visibility shopaccess" <?=($key_==$access_['shopdefault'])?'':'style="display:none"';?>>
                                <label for="inputPassword" class="col-form-label"><?=$key;?>:</label>
                                <input type="text" class="form-control shop-access-value"  name="access[shop][<?=$key_;?>][<?=$key;?>]" id="input-shop-<?=$key_;?>-<?=$key;?>" value="">
                            </div>
                        <?php endforeach; endforeach; ?>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Send message</button>
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                </div>
            </form>
        </div>
    </div>
</div>
<div class="modal fade" id="formRoleModal" tabindex="-1" aria-labelledby="formUserModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="formUserModalLabel">Create New User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= base_url('users/createRole'); ?> " method="post">
                <div class="modal-body">
                    <input type="hidden" name="roleID" id="roleID">
                    <div class="mb-3">
                        <label for="inputRoleName" class="form-label">Add Role</label>
                        <input type="text" class="form-control" id="inputRoleName" name="inputRoleName" placeholder="Role Name">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Save Role</button>
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                </div>
            </form>

        </div>
    </div>
</div>
<script>
    $(document).ready(function() {
        $(".btnAdd").click(function() {
            $('#formUserModalLabel').html('Create New User');
            $('.modal-footer button[type=submit]').html('Save Role');
            $('#userID').val('');
            $('#inputFullname').val('');
            $('#inputUsername').val('');
            $('#inputRole').val('');
            $('.shop-access-value').val('');
            $('.access-value').val('');
            $('#default').val('');
            $('#shopdefault').val('');

            $('.shopaccess').hide();
            $('.access').hide();
        });
        $(".btnEdit").click(function() {
            const userId = $(this).data('id');
            const fullname = $(this).data('fullname');
            const username = $(this).data('username');
            const role = $(this).data('role');
            
            $('#modalTitle').html('form Data User');
            $('.modal-footer button[type=submit]').html('Update User');
            $('.modal-content form').attr('action', '<?= base_url('users/updateUser') ?>');
            $('#userID').val(userId);
            $('#inputFullname').val(fullname);
            $('#inputUsername').val(username);
            $('#inputUsername').attr('readonly', true);
            $('#inputPassword').attr('required', false);
            $('#inputRole').val(role);

            $('#default').val(access[userId]['default']);
            $('.'+access[userId]['default']+'-visibility').show();
            $('#shopdefault').val(access[userId]['shopdefault']);
            $('.shop-'+access[userId]['shopdefault']+'-visibility').show();
            $('#zonedefault').val(access[userId]['zonedefault']);

            for( const item in access[userId] ){
                if(typeof access[userId][item] === 'object'){
                    for( const item_ in access[userId][item]){
                        $('#input-'+item+'-'+item_).val(access[userId][item][item_]);
                    }
                }
            }

            for( const item in access[userId]['shop'] ){
                if(typeof access[userId]['shop'][item] === 'object'){
                    for( const item_ in access[userId]['shop'][item]){
                        $('#input-shop-'+item+'-'+item_).val(access[userId]['shop'][item][item_]);
                    }
                }
            }

        });

        $(".btnAddRole").click(function() {
            $('#formUserModalLabel').html('Create New Role');
            $('.modal-content form').attr('action', '<?= base_url('users/createRole') ?>');
            $('.modal-footer button[type=submit]').html('Save Role');
            $('#roleID').val('');
            $('#inputRoleName').val('');
        });
        $(".btnEditRole").click(function() {
            const roleID = $(this).data('id');
            const inputRoleName = $(this).data('role');
            $('#modalTitle').html('Update Data Role');
            $('.modal-footer button[type=submit]').html('Update role');
            $('.modal-content form').attr('action', '<?= base_url('users/updateRole') ?>');
            $('#roleID').val(roleID);
            $('#inputRoleName').val(inputRoleName);
        });
        $("#default").on('change',function() {
            //console.log(this.value);
            $('.access').hide();
            $('.'+this.value+'-visibility').show();
        });
        $("#shopdefault").on('change',function() {
            //console.log(this.value);
            $('.shopaccess').hide();
            $('.shop-'+this.value+'-visibility').show();
        });
        
    });
</script>
<?= $this->endSection(); ?>