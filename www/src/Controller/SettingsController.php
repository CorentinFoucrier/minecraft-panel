<?php

namespace App\Controller;

use Core\Controller\Controller;

class SettingsController extends Controller
{
    public function __construct()
    {
        $this->loadModel('user', 'role', 'userRole', 'rolePermission', 'permission');
    }

    public function show(): void
    {
        $this->hasPermission('settings');
        $users = $this->getUsersRole();
        $roles = $this->role->selectEverything(true);
        $perms = $this->permission->selectEverything(true);
        $currentRank = $this->currentRole('rank');
        array_shift($users);
        array_shift($roles);
        $this->render('settings', [
            'title' => $this->lang('settings.title'),
            'roles' => $roles,
            'users' => $users,
            'perms' => $perms,
            'currentRank' => $currentRank
        ]);
    }

    /**
     * Route: /settings/add_new_user
     * Method: POST
     *
     * @return void
     */
    public function addNewUser(): void
    {
        $token = $_POST['token'];
        $role = htmlspecialchars($_POST['role']);
        $username = htmlspecialchars($_POST['username']);
        if ($token === $_SESSION['token']) {
            if ($this->hasPermission('settings__create_user', false)) {
                if ($roleEntity = $this->role->select(['name' => $role])) {
                    if ($roleEntity->getRank() !== $this->currentRole('rank')) {
                        // Prevent current user attribute a role greater than his current role
                        if ($roleEntity->getRank() > $this->currentRole('rank')) {
                            if (!$this->user->select(['username' => $username])) { // Username must be unique
                                $generatedPwd = bin2hex(random_bytes(4));
                                $generatedPwdHash = password_hash($generatedPwd, PASSWORD_ARGON2ID);
                                $createUser = $this->user->create([
                                    'username' => $username,
                                    'password' => $generatedPwdHash
                                ]);
                                $userId = $this->user->lastId();
                                $createUserRole = $this->userRole->create([
                                    'role_id' => $roleEntity->getId(),
                                    'user_id' => $userId
                                ]);
                                if ($createUser && $createUserRole) {
                                    $this
                                        ->echoJsonData('success')
                                        ->addToast($this->lang('settings.addNewUser.create.message', $username), $this->lang('settings.addNewUser.create.title'))
                                        ->add('generatedPassword', $generatedPwd)
                                        ->echo();
                                } else {
                                    $this->echoJsonData('error')->addToast($this->lang('general.error.retry'), $this->lang('general.error.occured'))->echo();
                                }
                            } else {
                                $this->echoJsonData('invalid')->addToast($this->lang('settings.addNewUser.userExist'))->echo();
                            }
                        } else {
                            $this->echoJsonData('error')->addToast($this->lang('settings.addNewUser.error.createGreater'))->echo();
                        }
                    } else {
                        $this->echoJsonData('error')->addToast($this->lang('settings.addNewUser.error.sameRole'))->echo();
                    }
                } else {
                    $this->echoJsonData('invalid')->addToast($this->lang('settings.addNewUser.error.invalid.message'), $this->lang('settings.addNewUser.error.invalid.title'))->echo();
                }
            } else {
                $this->echoJsonData('forbidden')->addToast($this->lang('general.error.forbidden'))->echo();
            }
        } else {
            $this->echoJsonData('error')->addToast($this->lang('general.error.badToken'))->echo();
        }
    }

    /**
     * Route: /settings/edit_user_role
     * Method: POST
     *
     * @return void
     */
    public function editUserRole(): void
    {
        if (!empty($_POST)) {
            $username = htmlspecialchars($_POST['username']);
            $role = htmlspecialchars($_POST['role']);
            $token = $_POST['token'];
            if ($token === $_SESSION['token']) {
                if ($this->hasPermission('settings__edit_user_role', false)) {
                    if ($userEntity = $this->user->select(['username' =>  $username])) { // Targeted user
                        if ($roleEntity = $this->role->select(['name' => $role])) { // Role to attribute
                            // Prevent a user to edit a role greater than his current role
                            if ($roleEntity->getRank() > $this->currentRole('rank')) {
                                if ($roleEntity->getRank() !== $this->currentRole('rank')) {
                                    if ($roleEntity->getId() !== 1) { // Check if targeted role isn't 'owner'
                                        if ($this->userRole->updateBy(
                                            ['user_id' => $userEntity->getId()],
                                            ['role_id' => $roleEntity->getId()]
                                        )) {
                                            $this->echoJsonData('success')->addToast($this->lang('settings.editUserRole.edit'))->echo();
                                        } else {
                                            $this->echoJsonData('error')->addToast($this->lang('general.error.retry'), $this->lang('general.error.occured'))->echo();
                                        }
                                    } else {
                                        $this->echoJsonData('error')->addToast($this->lang('general.error.retry'), $this->lang('general.error.occured'))->echo();
                                    }
                                } else {
                                    $this->echoJsonData('error')->addToast($this->lang('settings.editUserRole.error.sameRole'))->echo();
                                }
                            } else {
                                $this->echoJsonData('error')->addToast($this->lang('settings.editUserRole.error.greaterRole'))->echo();
                            }
                        } else {
                            $this->echoJsonData('error')->addToast($this->lang('general.error.reload'), $this->lang('settings.editUserRole.roleDoesNotExist'))->echo();
                        }
                    } else {
                        $this->echoJsonData('error')->addToast($this->lang('general.error.reload'), $this->lang('settings.editUserRole.userDoesNotExist'))->echo();
                    }
                } else {
                    $this->echoJsonData('error')->addToast($this->lang('general.error.forbidden'))->echo();
                }
            } else {
                $this->echoJsonData('error')->addToast($this->lang('general.error.badToken'))->echo();
            }
        }
    }

    /**
     * Route: /settings/delete_user
     * Method: POST
     *
     * @return void
     */
    public function deleteUser(): void
    {
        if (!empty($_POST)) {
            $username = htmlspecialchars($_POST['username']);
            $token = $_POST['token'];
            if ($token === $_SESSION['token']) {
                if ($this->hasPermission('settings__delete_user', false)) {
                    if ($username !== $_SESSION['username']) {
                        if ($user = $this->user->select(['username' => $username])) {
                            if ($this->user->deleteById($user->getId())) {
                                $this->echoJsonData('deleted')->addToast($this->lang('settings.deleteUser', $username))->echo();
                            } else {
                                $this->echoJsonData('error')->addToast($this->lang('general.error.retry'), $this->lang('general.error.occured'))->echo();
                            }
                        } else {
                            $this->echoJsonData('notExist')->addToast($this->lang('settings.deleteUser.userDoesNotExist'))->echo();
                        }
                    } else {
                        $this->echoJsonData('error')->addToast($this->lang('settings.deleteUser.error.self'))->echo();
                    }
                } else {
                    $this->echoJsonData('error')->addToast($this->lang('general.error.forbidden'))->echo();
                }
            } else {
                $this->echoJsonData('error')->addToast($this->lang('general.error.badToken'))->echo();
            }
        }
    }

    /**
     * Route: /settings/save_roles_order
     * Method: POST
     *
     * @return void
     */
    public function saveRolesOrder(): void
    {
        if (!empty($_POST)) {
            $token = $_POST['token'];
            $json = $_POST['data'];
            if ($token === $_SESSION['token']) {
                $data = json_decode($json);
                // To counter malisious users to change the POST request and put two "owner"
                if (count(array_unique($data)) === count($data)) {
                    // Array index = rank number
                    if ($data[0] === "owner") {
                        // Check if rank in data[n] is the same of current user role (prevent same role to be reorder)
                        if ($data[$this->currentRole('rank')] === $this->currentRole('name')) {
                            for ($i = 1; $i < count($data); $i++) {
                                $role = htmlspecialchars($data[$i]);
                                $this->role->updateBy(["name" => $role], ["rank" => $i]);
                                if ($i === count($data) - 1) {
                                    $this->echoJsonData('success')->addToast($this->lang('settings.saveRolesOrder.changes'))->echo();
                                }
                            }
                        } else {
                            $this->echoJsonData('error')->addToast($this->lang('settings.saveRolesOrder.error.moveOwnRole'))->echo();
                        }
                    } else {
                        $this->echoJsonData('error')->addToast('Invalid order', 'Internal server error')->echo(); // this error can't be trigged with a normal user behavior.
                    }
                } else {
                    $this->echoJsonData('error')->addToast('Data have some duplicates!', 'Internal server error')->echo(); // this error can't be trigged with a normal user behavior.
                }
            } else {
                $this->echoJsonData('error')->addToast($this->lang('general.error.badToken'))->echo();
            }
        }
    }

    /**
     * Route: /settings/add_new_role
     * Method: POST
     *
     * @return void
     */
    public function addNewRole(): void
    {
        if (!empty($_POST)) {
            $token = $_POST['token'];
            $role = htmlspecialchars($_POST['role']);
            if ($token === $_SESSION['token']) {
                if ($this->hasPermission('settings__create_role', false)) {
                    $newRank = $this->role->last('rank') + 1;
                    $createRole = $this->role->create([
                        'name' => $role,
                        'rank' => $newRank
                    ]);
                    if ($createRole) {
                        $this->echoJsonData('success')->addToast($this->lang('settings.addNewRole.create', $role))->add('rank', "$newRank")->echo();
                    } else {
                        $this->echoJsonData('error')->addToast($this->lang('general.error.retry'), $this->lang('general.error.occured'))->echo();
                    }
                } else {
                    $this->echoJsonData('error')->addToast($this->lang('general.error.forbidden'))->echo();
                }
            } else {
                $this->echoJsonData('error')->addToast($this->lang('general.error.badToken'))->echo();
            }
        }
    }

    /**
     * Route: /settings/get_role_permission
     * Method: POST
     *
     * @return void
     */
    public function getRolePermission(): void
    {
        if (!empty($_POST)) {
            $token = $_POST['token'];
            $role = htmlspecialchars($_POST['role']);
            if ($token === $_SESSION['token']) {
                if ($this->hasPermission('settings__edit_role_permissions', false)) {
                    if ($role !== $this->currentRole('name')) {
                        $role_id = $this->role->select(['name' => $role])->getId();
                        $tables = ['role_permission'];
                        $on = ['id' => 'permission_id'];
                        $where = ['role_id' => $role_id];
                        $perms = $this->permission->join($tables, $on, $where, true);
                        if ($perms) {
                            for ($i = 0; $i < count($perms); $i++) {
                                $perm = $perms[$i];
                                $permissions[] = $perm->getId();
                            }
                            $this->echoJsonData('success')->add('permissions', $permissions)->echo();
                        } else {
                            $this->echoJsonData('warning')->addToast($this->lang('settings.getRolePermission.warning.message'), $this->lang('settings.getRolePermission.warning.title'))->echo();
                        }
                    } else {
                        $this->echoJsonData('error')->addToast($this->lang('settings.getRolePermission.error.editOwnRole'))->echo();
                    }
                } else {
                    $this->echoJsonData('error')->addToast($this->lang('general.error.forbidden'))->echo();
                }
            } else {
                $this->echoJsonData('error')->addToast($this->lang('general.error.badToken'))->echo();
            }
        }
    }

    /**
     * Route: /settings/edit_role_permission
     * Method: POST
     *
     * @return void
     */
    public function editRolePermission(): void
    {
        if (!empty($_POST)) {
            $token = $_POST['token'];
            $checked = $_POST['checked']; // Boolean string
            $role = htmlspecialchars($_POST['role']);
            $permission = htmlspecialchars($_POST['permission']);
            if ($token === $_SESSION['token']) {
                if ($this->hasPermission('settings__edit_role_permissions', false)) {
                    $role_id = $this->role->select(['name' => $role])->getId();
                    $permission_id = $this->permission->select(['name' => $permission])->getId();
                    if ($checked === "true") {
                        $createRolePerm = $this->rolePermission->create([
                            'role_id' => $role_id,
                            'permission_id' => $permission_id
                        ]);
                        if (!$createRolePerm) {
                            $this->echoJsonData('error')->addToast($this->lang('general.error.retry'), $this->lang('general.error.occured'))->echo();
                        }
                    } elseif ($checked === "false") {
                        $removeRolePermission = $this->rolePermission->deleteAnd([
                            "role_id" => $role_id,
                            "permission_id" => $permission_id
                        ]);
                        if (!$removeRolePermission) {
                            $this->echoJsonData('error')->addToast($this->lang('general.error.retry'), $this->lang('general.error.occured'))->echo();
                        }
                    } else {
                        $this->echoJsonData('error')->addToast($this->lang('general.error.retry'), $this->lang('general.error.occured'))->echo();
                    }
                } else {
                    $this->echoJsonData('error')->addToast($this->lang('general.error.forbidden'))->echo();
                }
            } else {
                $this->echoJsonData('error')->addToast($this->lang('general.error.badToken'))->echo();
            }
        }
    }

    /**
     * Route: /settings/delete_role
     * Method: POST
     *
     * @return void
     */
    public function deleteRole(): void
    {
        if (!empty($_POST)) {
            $token = $_POST['token'];
            $role = htmlspecialchars($_POST['role']);
            if ($token === $_SESSION['token']) {
                if ($this->hasPermission('settings__delete_role', false)) {
                    $selectedRole = $this->role->select(['name' => $role]);
                    $roleContainsUsers = $this->userRole->select(['role_id' => $selectedRole->getId()]);
                    if (!$roleContainsUsers) {
                        // Prevent a user to delete his own role
                        if ($selectedRole->getName() !== $this->currentRole('name')) {
                            // Prevent a user to delete a role greater than his current role
                            if ($selectedRole->getRank() > $this->currentRole('rank')) {
                                if ($this->role->deleteById($selectedRole->getId())) {
                                    $this->echoJsonData('success')->addToast($this->lang('settings.deleteRole.deleted'))->echo();
                                } else {
                                    $this->echoJsonData('error')->addToast($this->lang('general.error.retry'), $this->lang('general.error.occured'))->echo();
                                }
                            } else {
                                $this->echoJsonData('error')->addToast($this->lang('settings.deleteRole.error.greater'))->echo();
                            }
                        } else {
                            $this->echoJsonData('error')->addToast($this->lang('settings.deleteRole.error.ownRole'))->echo();
                        }
                    } else {
                        $this->echoJsonData('error')->addToast($this->lang('settings.deleteRole.error.roleContainUsers'))->echo();
                    }
                } else {
                    $this->echoJsonData('error')->addToast($this->lang('general.error.forbidden'))->echo();
                }
            } else {
                $this->echoJsonData('error')->addToast($this->lang('general.error.badToken'))->echo();
            }
        }
    }
}
