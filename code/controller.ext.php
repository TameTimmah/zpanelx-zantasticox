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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
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
                $options .= '<option selected>'.$category['cat_name'].'</option>';
            } else {
                $options .= '<option>'.$category['cat_name'].'</option>';
            }
            
            // If category should be shown
            if ($_GET['cat'] === $category['cat_name'] || $_GET['cat'] === NULL || $_GET['cat'] === 'All Applications') {
                
                // Show title and description
                $html .= '<section class="zanx_mainview">';
                $html .= '<h3>'.$category['cat_name'].'</h3>';
                $html .= '<p>'.$category['cat_desc'].'</p>';
                
                // Get enabled apps in category
                $sql2 = $zdbh->prepare("SELECT * FROM zanx_apps WHERE cat_id = :cat_id AND app_enabled = 1");
                $sql2->bindParam(':cat_id',$category['cat_id']);
                $sql2->execute();
                
                // For every app in category
                while ($app = $sql2->fetch()) {
                    $html .= '<a href="?module=zantasticox';
                    if ($_GET['cat'] === $category['cat_name']) {
                        $html .= '&cat='.$category['cat_name'];
                    }
                    $html .= '&act=view&app='.strtolower($app['app_name']).'">
                        <img src="modules/zantasticox/apps/'.strtolower($app['app_name']).'/smallicon.png" width="50" height="50" alt="'.$app['app_name'].' Icon">
                        <h5>'.$app['app_name'].'</h5>
                        <h6>'.$app['app_type'].'</h6>
                    </a>';
                }
                
                $html .= '</section">';
            }
        }
        
        // Show top bar
        $top_bar = '<div id="zanx_topbar">
            <div class="pull-left">
                <select class="form-control" onchange="var str1=\'?module=zantasticox&cat=\';var str2=this.options[this.selectedIndex].value;location=str1.concat(str2);">
                    <option>All Applications</option>'
                    . $options .
                '</select>
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
                    <input type="text" class="form-control" placeholder="Search Apps" name="query" value="'.htmlentities($_GET['query']).'">
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
            $sql->bindParam(':query',$_GET['query']);
            $sql->execute();
            
            $html .= '<p>You searched for &quot;'.htmlentities($_GET['query']).'&quot;. Any results found are shown below.</p><section class="zanx_mainview">';
            
            // For every app
            while ($result = $sql->fetch()) {
                $html .= '<a href="?module=zantasticox';
                if ($_GET['cat'] !== NULL) {
                    $html .= '&cat='.$_GET['cat'];
                }
                $html .= '&act=view&app='.strtolower($result['app_name']).'">
                    <img src="modules/zantasticox/apps/'.strtolower($result['app_name']).'/smallicon.png" width="50" height="50" alt="'.$result['app_name'].' Icon">
                    <h5>'.$result['app_name'].'</h5>
                    <h6>'.$result['app_type'].'</h6>
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
        
        // App HTML
        $html .= '<div id="zanx_topbar">
            <div class="pull-left">
                <a href="?module=zantasticox';
                if ($_GET['cat'] !== NULL) {
                    $html .= '&cat='.$_GET['cat'];
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
            <img src="modules/zantasticox/apps/'.strtolower($app['app_name']).'/largeicon.png" width="100" height="100" alt="'.$app['app_name'].' Icon">
            <h3>'.$app['app_name'].'</h3>
            <p>'.$app['app_desc'].'</p>
        </div>
        
        <div class="text-center" id="zanx_buttons">
            <a href="'.$app['app_site'].'" target="_blank" class="btn btn-default">Visit Website</a>
            <a href="?module=zantasticox&app='.$app['app_name'].'&act=install" class="btn btn-primary">Install Application</a>
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
                    <td>'.$app['app_name'].'</td>
                    <td>'.$app['app_type'].'</td>
                    <td>'.$app['app_version'].'</td>
                    <td>'.$app['app_updated'].'</td>
                    <td>';
                    if ($app['app_db'] != 1) {
                        $html.='Not ';
                    }
                    $html.='Required</td>
                </tr>
            </tbody>
        </table>';
        
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
        
        // Get user's domains
        $sql2 = $zdbh->prepare("SELECT * FROM x_vhosts WHERE vh_acc_fk = :zpuid and vh_active_in='1' and vh_deleted_ts is NULL and vh_directory_vc != ''");
        $sql2->bindParam(':zpuid', $_SESSION['zpuid']);
        $sql2->execute();
        
        // Add domains to dropdown
        while ($vhost_details = $sql2->fetch()) {
            $options .= "<option>".$vhost_details['vh_name_vc']."</option>";
        }

        // Display form
        $html .= '
            <h3>You are about to install '.$app['app_name'].'!</h3>
            <p>This install wizard will create all the needed files and directories for '.$app['app_name'].'';if($app['app_db']==1){$html.=' but requires you to setup the database manually';}$html.='.

            <form role="form" id="zanx_installform" method="post">
              <div class="form-group">
                <label for="zanx_domain">Please select the domain to install '.$app['app_name'].' to:</label>
                <br>
                <select class="form-control" name="zanx_domain">
                    '.$options.'
                </select>
              </div>
              <div class="form-group">
                <label>Would you like to install '.$app['app_name'].' into a subfolder?</label>
                <br>
                <label class="zanx_radio"><input type="radio" name="zanx_subfolder_toggle" value="no" id="zanx_subfolder_no" checked="yes" onchange="zanx_subfoldercheck()"> No</label>
                <br>
                <label class="zanx_radio"><input type="radio" name="zanx_subfolder_toggle" value="yes" id="zanx_subfolder_yes" onchange="zanx_subfoldercheck()"> Yes</label>
              </div>

              <script>function zanx_subfoldercheck(){if(document.getElementById("zanx_subfolder_no").checked){document.getElementById("zanx_installfolder").style.display="none"}else{document.getElementById("zanx_installfolder").style.display="block"}}</script>

              <div class="form-group" id="zanx_installfolder">
                <label for="zanx_subfolder">Please enter a subfolder to install '.$app['app_name'].' to:</label>
                <br>
                <input class="form-control" type="text" name="zanx_subfolder">
                <span class="help-block">For example, type "blog/happy" to install '.$app['app_name'].' into "yourdomain.com/blog/happy".</span>
              </div>
              <p><i>By installing this application:</i></p>
              <ul>
              <li>You agree to the application&apos;s end user license agreement.</li>
              <li>You accept all files within the install directory will be permanently deleted.</li>
              </ul>
              <a href="?module=zantasticox';
                if($_GET['cat'] !== NULL){$html .= '&cat='.$_GET['cat'];}
                $html .= '&act=view&app='.$app['app_name'].'" class="btn btn-default">Return to details</a> <button type="submit" class="btn btn-primary">Install Application</button>
            </form>
        ';
        return $html;
    }
    
    // Do the install
    static function getInstall() {
        // Still a work in progress
        // A bit messy...but it works (on linux)!
        global $zdbh;
        global $controller;
        
        // Get app information
        $sql = $zdbh->prepare("SELECT * FROM zanx_apps WHERE app_name = :app_name");
        $sql->bindParam(':app_name', $_GET['app']);
        $sql->execute();
        $app = $sql->fetch();
        
        $account_details = ctrl_users::GetUserDetail($_SESSION['zpuid']);

        $zip_path = realpath('./modules/zantasticox/apps/'.strtolower($app['app_name']).'/archive.zip');
        $extract_path = ctrl_options::GetOption('hosted_dir').$account_details['username'].'/public_html/'.str_replace(".","_",$_POST['zanx_domain']);
        
        if ($_POST['zanx_subfolder_toggle']='yes') {
            $extract_path .= '/'.$_POST['zanx_subfolder'];
        }

        if ($zip_path) {
            mkdir($extract_path);
            $real_extract_path = realpath($extract_path);

            if($real_extract_path) {

                $zip = new ZipArchive;
                $zip->open($zip_path);
                $zip->extractTo($real_extract_path);
                $zip->close();
                $html .= '<h3>Installed '.$app['app_name'].'!</h3>';
            }
            else{
                $html .= '<p>Error - Could not find/create install directory</p>';
            }
        }else {
            $html .= '<p>Error - Could not find install zip.</p>';
        }
        return $html;
    }
    
    // Display 404 error
    static function get404() {
        
        header("HTTP/1.0 404 Not Found");
        return '<h1>Requested Page Not Found!</h1>';
        
    }
    
    // Handles what is displayed
    static function getModuleDisplay() {
        
        if($_GET['act']===NULL) {
            // View app list
            return module_controller::getMainView();
        }
        elseif($_GET['act']==='view') {
            // View app details
            return module_controller::getAppView();
        }
        elseif($_GET['act']==='install' & $_POST['zanx_domain'] != NULL) {
            // Install application
            return module_controller::getInstall();
        }
        elseif($_GET['act']==='install') {
            // Show install wizard
            return module_controller::getInstallWizard();
        }
        elseif($_GET['act']==='search') {
            // Show search results
            return module_controller::getSearchResults();
        }
        else {
            // Show 404
            return module_controller::get404();
        }
        
    }
    
}