<?php

/*
 * Zantastico X
 * A Open Source Module for ZPanel
 * Copyright (C) 2014 Jacob Gelling
 * 
 * This module is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * This module is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this module.  If not, see <http://www.gnu.org/licenses/>.
 */

include '../../../cnf/db.php';
include '../../../inc/dbc.inc.php';

class module_controller {

    static function getModuleName() {
        $module_name = ui_module::GetModuleName();
        return $module_name;
    }

    static function getModuleIcon() {
        $module_icon = 'modules/zantasticox/assets/icon.png';
        return $module_icon;
    }

    static function getModuleDesc() {
        $message = ui_language::translate(ui_module::GetModuleDescription());
        return $message;
    }

    // Display list of apps
    static function getMainView() {
        global $zdbh;

        // Get categories
        $sql = $zdbh->prepare("SELECT * FROM zanx_categories");
        $sql->execute();

        // For every category
        while ($category = $sql->fetch()) {

            // Add options to add to the dropdown in the top bar
            if ($_GET['cat'] === $category['cat_name']) {
                $options .= '<option selected>' . $category['cat_name'] . '</option>';
            } else {
                $options .= '<option>' . $category['cat_name'] . '</option>';
            }

            // If category should be shown
            if ($_GET['cat'] === $category['cat_name'] || $_GET['cat'] === NULL || $_GET['cat'] === 'All Applications') {

                // Show title and description
                $html .= '<section class="zanx_mainview">';
                $html .= '<h3>' . $category['cat_name'] . '</h3>';
                $html .= '<p>' . $category['cat_desc'] . '</p>';

                // Get enabled apps in category
                $sql2 = $zdbh->prepare("SELECT * FROM zanx_apps WHERE cat_id = :cat_id AND app_enabled = 1");
                $sql2->bindParam(':cat_id', $category['cat_id']);
                $sql2->execute();

                // For every app in category
                while ($app = $sql2->fetch()) {
                    $html .= '<a href="?module=zantasticox';
                    if ($_GET['cat'] === $category['cat_name']) {
                        $html .= '&cat=' . $category['cat_name'];
                    }
                    $html .= '&act=view&app=' . strtolower($app['app_name']) . '">
                        <img src="modules/zantasticox/apps/' . strtolower($app['app_name']) . '/smallicon.png" width="50" height="50" alt="' . $app['app_name'] . ' Icon">
                        <h5>' . $app['app_name'] . '</h5>
                        <h6>' . $app['app_type'] . '</h6>
                    </a>';
                }

                $html .= '</section>';
            }
        }

        // Show top bar
        $top_bar = '<div id="zanx_topbar">
            <div class="pull-left">
                <select class="form-control" onchange="var str1=\'?module=zantasticox&cat=\';var str2=this.options[this.selectedIndex].value;location=str1.concat(str2);">
                    <option>All Applications</option>'
                . $options .
                '</select>
            </div>';
        $account_details = ctrl_users::GetUserDetail($_SESSION['zpuid']);
        if ($account_details['usergroupid'] == 1) {
            $top_bar .= '<a href="?module=zantasticox&act=admin" class="btn btn-default" id="zanx_manage">Admin Area</a>';
        }
        $top_bar .= '<form class="pull-right form-inline" role="form" method="get">
                <div class="form-group">
                    <input type="hidden" name="module" value="zantasticox">
                    <input type="hidden" name="act" value="search">
                    <input type="text" class="form-control" placeholder="Search Apps" name="query">
                    <button type="submit" class="btn btn-default">Search</button>
                </div>
            </form>
        </div>
        <hr>';

        return $top_bar . $html;
    }

    // Display search results
    static function getSearchResults() {
        // Top bar HTML
        $html = '<div id="zanx_topbar">
            <div class="pull-left">
                <a href="?module=zantasticox" class="btn btn-default">Return to list</a>
            </div>
            <form class="pull-right form-inline" role="form" method="get">
                <div class="form-group">
                    <input type="hidden" name="module" value="zantasticox">
                    <input type="hidden" name="act" value="search">
                    <input type="text" class="form-control" placeholder="Search Apps" name="query" value="' . htmlentities($_GET['query']) . '">
                    <button type="submit" class="btn btn-default">Search</button>
                </div>
            </form>
        </div>
        <hr>';

        if ($_GET['query'] == NULL) {
            $html .= '<p>Please enter an app to search for or <a href="?module=zantasticox">return to the list</a>.</p>';
        } else {
            global $zdbh;

            // Get apps which contain the query
            $sql = $zdbh->prepare("SELECT * FROM zanx_apps WHERE app_name = :query OR app_type = :query");
            $sql->bindParam(':query', $_GET['query']);
            $sql->execute();

            $html .= '<p>You searched for &quot;' . htmlentities($_GET['query']) . '&quot;. Any results found are shown below.</p><section class="zanx_mainview">';

            // For every app
            while ($result = $sql->fetch()) {
                $html .= '<a href="?module=zantasticox';
                if ($_GET['cat'] !== NULL) {
                    $html .= '&cat=' . $_GET['cat'];
                }
                $html .= '&act=view&app=' . strtolower($result['app_name']) . '">
                    <img src="modules/zantasticox/apps/' . strtolower($result['app_name']) . '/smallicon.png" width="50" height="50" alt="' . $result['app_name'] . ' Icon">
                    <h5>' . $result['app_name'] . '</h5>
                    <h6>' . $result['app_type'] . '</h6>
                </a>';
            }
            $html .= '</section>';
        }
        return $html;
    }

    // Display information about app
    static function getAppView() {

        global $zdbh;

        // Get app information
        $sql = $zdbh->prepare("SELECT * FROM zanx_apps WHERE app_name = :app_name");
        $sql->bindParam(':app_name', $_GET['app']);
        $sql->execute();
        $app = $sql->fetch();

        // Ensure valid application is selected
        if ($app) {

            // App HTML
            $html .= '<div id="zanx_topbar">
            <div class="pull-left">
                <a href="?module=zantasticox';
            if ($_GET['cat'] !== NULL) {
                $html .= '&cat=' . $_GET['cat'];
            }
            $html.='" class="btn btn-default">Return to list</a>
            </div>
            <form class="pull-right form-inline" role="form" method="get">
                <div class="form-group">
                    <input type="hidden" name="module" value="zantasticox">
                    <input type="hidden" name="act" value="search">
                    <input type="text" class="form-control" placeholder="Search Apps" name="query">
                    <button type="submit" class="btn btn-default">Search</button>
                </div>
            </form>
        </div>
        <hr>
        
        <div id="zanx_summary">
            <img src="modules/zantasticox/apps/' . strtolower($app['app_name']) . '/largeicon.png" width="100" height="100" alt="' . $app['app_name'] . ' Icon">
            <h3>' . $app['app_name'] . '</h3>
            <p>' . $app['app_desc'] . '</p>
        </div>
        
        <div class="text-center" id="zanx_buttons">
            <a href="' . $app['app_site'] . '" target="_blank" class="btn btn-default">Visit Website</a>
            <a href="?module=zantasticox&app=' . strtolower($app['app_name']) . '&act=install" class="btn btn-primary">Install Application</a>
        </div>
        
        <table class="table" id="zanx_details">
            <thead>
                <tr>
                    <th>Application</th>
                    <th>Type</th>
                    <th>Version</th>
                    <th>Last Updated</th>
                    <th>Database</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>' . $app['app_name'] . '</td>
                    <td>' . $app['app_type'] . '</td>
                    <td>' . $app['app_version'] . '</td>
                    <td>' . $app['app_updated'] . '</td>
                    <td>';
            if ($app['app_db'] != 1) {
                $html.='Not ';
            }
            $html.='Required</td>
                </tr>
            </tbody>
        </table>';
        } else {
            $html = '<h3>Error - Invalid application selected.</h3>';
        }
        return $html;
    }

    // Display install wizard
    static function getInstallWizard() {
        global $zdbh;

        // Get app information
        $sql = $zdbh->prepare("SELECT * FROM zanx_apps WHERE app_name = :app_name");
        $sql->bindParam(':app_name', $_GET['app']);
        $sql->execute();
        $app = $sql->fetch();

        // Ensure valid application is selected
        if ($app) {

            // Get user's domains
            $sql2 = $zdbh->prepare("SELECT * FROM x_vhosts WHERE vh_acc_fk = :zpuid and vh_active_in='1' and vh_deleted_ts is NULL and vh_directory_vc != ''");
            $sql2->bindParam(':zpuid', $_SESSION['zpuid']);
            $sql2->execute();

            // Add domains to dropdown
            while ($vhost_details = $sql2->fetch()) {
                $options .= "<option>" . $vhost_details['vh_name_vc'] . "</option>";
            }

            // Display form
            $html .= '
            <h3>You are about to install ' . $app['app_name'] . '!</h3>
            <p>This install wizard will create all the needed files and directories for ' . $app['app_name'] . '';
            if ($app['app_db'] == 1) {
                $html.=' but requires you to setup the database manually';
            }$html.='.

            <form role="form" id="zanx_installform" method="post">
              <div class="form-group">
                <label for="zanx_domain">Please select the domain to install ' . $app['app_name'] . ' to:</label>
                <br>
                <select class="form-control" name="zanx_domain">
                    ' . $options . '
                </select>
              </div>
              <div class="form-group">
                <label>Would you like to install ' . $app['app_name'] . ' into a subfolder?</label>
                <br>
                <label class="zanx_radio"><input type="radio" name="zanx_subfolder_toggle" value="no" id="zanx_subfolder_no" checked="yes" onchange="zanx_subfoldercheck()"> No</label>
                <br>
                <label class="zanx_radio"><input type="radio" name="zanx_subfolder_toggle" value="yes" id="zanx_subfolder_yes" onchange="zanx_subfoldercheck()"> Yes</label>
              </div>

              <script>function zanx_subfoldercheck(){if(document.getElementById("zanx_subfolder_no").checked){document.getElementById("zanx_installfolder").style.display="none"}else{document.getElementById("zanx_installfolder").style.display="block"}}</script>

              <div class="form-group" id="zanx_installfolder">
                <label for="zanx_subfolder">Please enter a subfolder to install ' . $app['app_name'] . ' to:</label>
                <br>
                <input class="form-control" type="text" name="zanx_subfolder">
                <span class="help-block">For example, type "blog/happy" to install ' . $app['app_name'] . ' into "yourdomain.com/blog/happy".</span>
              </div>
              <p><i>By installing this application:</i></p>
              <ul>
              <li>You agree to the application&apos;s end user license agreement.</li>
              <li>You accept all files within the install directory will be permanently deleted.</li>
              </ul>
              <a href="?module=zantasticox';
            if ($_GET['cat'] !== NULL) {
                $html .= '&cat=' . $_GET['cat'];
            }
            $html .= '&act=view&app=' . strtolower($app['app_name']) . '" class="btn btn-default">Return to details</a> <button type="submit" class="btn btn-primary">Install Application</button>
            </form>
        ';
        } else {
            $html = '<h3>Error - Invalid application selected.</h3>';
        }
        return $html;
    }

    // Do the install
    static function getInstall() {
        global $zdbh;

        // Get app information
        $sql = $zdbh->prepare("SELECT * FROM zanx_apps WHERE app_name = :app_name");
        $sql->bindParam(':app_name', $_GET['app']);
        $sql->execute();
        $app = $sql->fetch();

        // Get specified domain
        $sql2 = $zdbh->prepare("SELECT * FROM x_vhosts WHERE vh_acc_fk = :zpuid and vh_active_in='1' and vh_deleted_ts is NULL and vh_directory_vc != '' and vh_name_vc = :domain");
        $sql2->bindParam(':zpuid', $_SESSION['zpuid']);
        $sql2->bindParam(':domain', $_POST['zanx_domain']);
        $sql2->execute();
        $vhost_details = $sql2->fetch();

        // Ensure valid domain and application are selected
        if ($app & $vhost_details) {

            // Generate zip path
            $zip_path = 'modules/zantasticox/apps/' . strtolower($app['app_name']) . '/archive.zip';

            if ($zip_path) {

                // Generate install path
                $account_details = ctrl_users::GetUserDetail($_SESSION['zpuid']);
                $extract_path = ctrl_options::GetOption('hosted_dir') . $account_details['username'] . '/public_html/' . str_replace(".", "_", $vhost_details['vh_name_vc']) . '/';

                if ($_POST['zanx_subfolder_toggle'] === 'yes') {
                    $subfolder = str_replace(".", "_", trim($_POST['zanx_subfolder'], '/')) . '/';
                    $extract_path .= $subfolder;
                }
                mkdir($extract_path);

                if ($extract_path) {

                    $zip = new ZipArchive;
                    $zip->open($zip_path);
                    $zip->extractTo($extract_path);
                    $zip->close();

                    $sysOS = php_uname('s');
                    if ($sysOS == 'Linux' || $sysOS == 'Unix') {
                        $zsudo = ctrl_options::GetOption('zsudo');
                        exec("$zsudo chown -R ftpuser:ftpgroup " . $extract_path);
                        exec("$zsudo chmod -R 777 " . $extract_path);
                    }

                    $html .= '<h3>' . $app['app_name'] . ' installed successfully!</h3>';
                    if ($app['app_db'] == 1) {
                        $html .= '<p>Remember to create a <a href="?module=mysql_databases">database</a> and <a href="?module=mysql_users">database user</a>.</p>';
                    }
                    $html .= '
                    <a href="?module=zantasticox" class="btn">Return to list</a>
                    <a href="http://' . $vhost_details['vh_name_vc'] . '/' . $subfolder . '" class="btn btn-primary" target="_blank">Visit your website</a>
                    ';
                } else {
                    $html .= '<p>Error - Could not find/create the install directory</p>';
                }
            } else {
                $html .= '<p>Error - Could not find the application zip.</p>';
            }
        } else {
            $html = '<h3>Error - Invalid domain or application selected.</h3>';
        }
        return $html;
    }

    // Display admin area
    static function getAdmin() {

        $account_details = ctrl_users::GetUserDetail($_SESSION['zpuid']);
        if ($account_details['usergroupid'] == 1) {

            global $zdbh;

            $html .= '<div id="zanx_topbar">
                    <div class="pull-left">
                        <a href="?module=zantasticox" class="btn btn-default">Return to list</a>
                    </div>
                </div>
                <hr>
                
                <ul class="nav nav-tabs">
                  <li class="active"><a href="#general" data-toggle="tab">General</a></li>
                  <li><a href="#applications" data-toggle="tab">Applications</a></li>
                  <li><a href="#categories" data-toggle="tab">Categories</a></li>
                </ul>
                
                <div class="tab-content" id="zanx_admin">
                    <div class="tab-pane active" id="general">
                    ...general
                </div>
                
                <div class="tab-pane" id="applications">
                <form method="post">
                    <table class="table table-striped" style="table-layout: fixed;">
                        <tr>
                            <th>Name</th>
                            <th>Type</th>
                            <th>Version</th>
                            <th></th>
                        </tr>';
            
            // Get apps
            $sql = $zdbh->prepare("SELECT * FROM zanx_apps");
            $sql->execute();

            // For every app
            while ($app = $sql->fetch()) {
                $html .= '<tr>
                    <td>' . $app['app_name'] . '</td>
                    <td>' . $app['app_type'] . '</td>
                    <td>' . $app['app_version'] . '</td>
                    <td><button name="zanx_app" class="btn btn-primary" type="submit" value="' . $app['app_name'] . '">Edit</button></td>
                </tr>';
            }
            
            $html .= '
                    </table></form>
                </div>
                
                <div class="tab-pane" id="categories">
                    <table class="table table-striped" style="table-layout: fixed;">
                        <tr>
                            <th>Name</th>
                            <th>Description</th>
                            <th></th>
                        </tr>';
                        
            // Get categories
            $sql2 = $zdbh->prepare("SELECT * FROM zanx_categories");
            $sql2->execute();
            
            // For every category
            while ($category = $sql2->fetch()) {
                $html .= '<tr>
                    <td>' . $category['cat_name'] . '</td>
                    <td>' . $category['cat_desc'] . '</td>
                    <td>  <a href="#edit" class="btn btn-primary">Edit</a> <a href="#delete" class="btn btn-danger">Delete</a>  </td>
                </tr>';
            }
            
            $html .= '</table>
                </div>
                
            </div>';
        } else {
            $html = '<h3>Error - You have the incorrect permissions to view this page.</h3>';
        }
        return $html;
    }
    
    // Display admin area
    static function getAdminApp() {
        
        $account_details = ctrl_users::GetUserDetail($_SESSION['zpuid']);
        if ($account_details['usergroupid'] == 1) {
            
            global $zdbh;
            
            // Get app information
            $sql = $zdbh->prepare("SELECT * FROM zanx_apps WHERE app_name = :app_name");
            $sql->bindParam(':app_name', $_POST['zanx_app']);
            $sql->execute();
            $app = $sql->fetch();
            
            if ($app) {
                $html .= '<form method="post">
                    
                <input type="text" value="' . $app['app_name'] . '">
                <input type="text" value="' . $app['app_type'] . '">
                </form>
                    
                ';
            } else {
                $html = '<h3>Error - Could not find specified application.</h3>';
            }
            
        } else {
            $html = '<h3>Error - You have the incorrect permissions to view this page.</h3>';
        }
        
        return $html;
    }
    
    // Display admin area
    static function getAdminCat() {
        $account_details = ctrl_users::GetUserDetail($_SESSION['zpuid']);
        if ($account_details['usergroupid'] == 1) {
            
            global $zdbh;
            
        } else {
            $html = '<h3>Error - You have the incorrect permissions to view this page.</h3>';
        }
        
        return $html;
    }

    // Display 404 error
    static function get404() {

        header("HTTP/1.0 404 Not Found");
        return '<h3>Error - Requested Page Not Found!</h3>';
    }

    // Handles what is displayed
    static function getModuleDisplay() {
        
        if ($_GET['act'] === NULL) {
            // View app list
            return module_controller::getMainView();
        } elseif ($_GET['act'] === 'view') {
            // View app details
            return module_controller::getAppView();
        } elseif ($_GET['act'] === 'install' & $_POST['zanx_domain'] != NULL) {
            // Install application
            return module_controller::getInstall();
        } elseif ($_GET['act'] === 'install') {
            // Show install wizard
            return module_controller::getInstallWizard();
        } elseif ($_GET['act'] === 'search') {
            // Show search results
            return module_controller::getSearchResults();
        } elseif ($_GET['act'] === 'admin' & $_POST['zanx_app'] != NULL) {
            // Install application
            return module_controller::getAdminApp();
        } elseif ($_GET['act'] === 'admin' & $_POST['zanx_cat'] != NULL) {
            // Install application
            return module_controller::getAdminCat();
        } elseif ($_GET['act'] === 'admin') {
            // Show admin settings
            return module_controller::getAdmin();
        } else {
            // Show 404
            return module_controller::get404();
        }
    }

}
