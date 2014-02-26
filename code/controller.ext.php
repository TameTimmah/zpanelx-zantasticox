<?php

/*
 * App Installer
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

class module_controller {
    
    static function getModuleName() {
        $module_name = ui_module::GetModuleName();
        return $module_name;
    }

    static function getModuleIcon() {
        global $controller;
        $module_icon = "modules/" . $controller->GetControllerRequest('URL', 'module') . "/assets/icon.png";
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
        $sql = $zdbh->prepare("SELECT * FROM x_ai_categories");
        $sql->execute();
        
        // For every category
        while ($category = $sql->fetch()) {
            
            // Add options to dropdown in the top bar
            if ($_GET['cat'] === $category['ai_name']) {
                $options .= '<option selected>'.$category['ai_name'].'</option>';
            }
            else {
                $options .= '<option>'.$category['ai_name'].'</option>';
            }
            
            // If category is selected in dropdown
            if ($_GET['cat'] === $category['ai_name'] || $_GET['cat'] === NULL || $_GET['cat'] === 'All Applications') {
                $html .= '<section class="mainview">';
                $html .= '<h3>'.$category['ai_name'].'</h3>';
                $html .= '<p>'.$category['ai_desc'].'</p>';
                
                // Get apps in category
                $types = unserialize(base64_decode($category['ai_types']));
                $ids = "'". implode("', '", $types) ."'";
                $query2 = "SELECT * FROM x_ai_apps WHERE ai_type IN ($ids)";
                $sql2 = $zdbh->prepare($query2);
                $sql2->execute();
                
                // For every app in category
                while ($rowdomains2 = $sql2->fetch()) {
                    
                    $html .= '<a href="?module=app_installer';
                    if($_GET['cat'] !== NULL){$html .= '&cat='.$_GET['cat'];}
                    $html .= '&act=view&app='.strtolower($rowdomains2['ai_name']).'">
                        <img src="modules/app_installer/apps/'.strtolower($rowdomains2['ai_name']).'/smallicon.png" width="50" height="50" alt="'.$rowdomains2['ai_name'].'">
                        <h5>'.$rowdomains2['ai_name'].'</h5>
                        <h6>'.$rowdomains2['ai_type'].'</h6>
                    </a>';
                    
                }
                
                $html .= '</section">';
                
            }
            
        }
        
        // Top bar HTML
        $top_bar = '<div id="app_topbar">
            <div class="pull-left">
                <select class="form-control" onchange="var str1 = \'?module=app_installer&cat=\';var str2 = this.options[this.selectedIndex].value;location = str1.concat(str2);">
                    <option>All Applications</option>'
                    . $options .
                '</select>
            </div>
            <form class="pull-right form-inline" role="form" method="get">
                <div class="form-group">
                    <input type="hidden" name="module" value="app_installer">
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
        
        global $zdbh;
        
        // Get app information
        $sql = $zdbh->prepare("SELECT * FROM x_ai_apps WHERE ai_name = :query");
        $sql->bindParam(':query', $_GET['query']);
        $sql->execute();
        $app_details = $sql->fetch();
        
        
        
        return $html;
        
    }
    
    // Display information about app
    static function getAppView() {
        
        global $zdbh;
        
        // Get app information
        $sql = $zdbh->prepare("SELECT * FROM x_ai_apps WHERE ai_name = :app_name");
        $sql->bindParam(':app_name', $_GET['app']);
        $sql->execute();
        $app_details = $sql->fetch();
        
        // App HTML
        $html .= '<div id="app_topbar">
            <div class="pull-left">
                <a href="?module=app_installer';if($_GET['cat'] !== NULL){$html .= '&cat='.$_GET['cat'];}$html.='" class="btn btn-default">Return to list</a>
            </div>
            <form class="pull-right form-inline" role="form" method="get">
                <div class="form-group">
                    <input type="hidden" name="module" value="app_installer">
                    <input type="hidden" name="act" value="search">
                    <input type="text" class="form-control" placeholder="Search Apps" name="query">
                    <button type="submit" class="btn btn-default">Search</button>
                </div>
            </form>
        </div>
        <hr>
        
        <div id="app_summary">
            <img src="modules/app_installer/apps/'.strtolower($app_details['ai_name']).'/largeicon.png" width="100" height="100" alt="'.$app_details['ai_name'].'">
            <h3>'.$app_details['ai_name'].'</h3>
            <p>'.$app_details['ai_desc'].'</p>
        </div>
        
        <div class="text-center" id="app_buttons">
            <a href="'.$app_details['ai_site'].'" target="_blank" class="btn btn-default">Visit Website</a>
            <a href="?module=app_installer&app='.$app_details['ai_name'].'&act=install" class="btn btn-primary">Install Application</a>
        </div>
        
        <table class="table" id="app_details">
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
                    <td>'.$app_details['ai_name'].'</td>
                    <td>'.$app_details['ai_type'].'</td>
                    <td>'.$app_details['ai_version'].'</td>
                    <td>'.$app_details['ai_updated'].'</td>
                    <td>';if ($app_details['ai_db']!==1){$html.='Not Required';}else{$html.='Required';}$html.='</td>
                </tr>
            </tbody>
        </table>';
        
        return $html;
        
    }
    
    // Display installer page for app
    static function getAppInstall() {
        // ---- WIP ---- //
        global $zdbh;
        
        // Get app information
        $sql = $zdbh->prepare("SELECT * FROM x_ai_apps WHERE ai_name = :app_name");
        $sql->bindParam(':app_name', $_GET['app']);
        $sql->execute();
        
        // Display app installer
        while ($rowdomains = $sql->fetch()) {
            $html .= '
                <h3>This is the installer for '.$rowdomains['ai_name'].'.</h3>
                <p>This module is under development and thus this page isn&apos;t functioning yet...</p>
            ';
        }
        return $html;
    }
    
    // Display 404 error
    static function get404() {
        
        header("HTTP/1.0 404 Not Found");
        return '<h1>Requested Page Not Found!</h1>';
        
    }
    
    // Handles what is displayed depending on the 'act' GET request.
    static function getModuleDisplay() {
        
        if($_GET['act']===NULL) {
            return module_controller::getMainView();
        }
        elseif($_GET['act']==='view') {
            return module_controller::getAppView();
        }
        elseif($_GET['act']==='install') {
            return module_controller::getAppInstall();
        }
        elseif($_GET['act']==='search') {
            return module_controller::getSearchResults();
        }
        else {
            return module_controller::get404();
        }
        
    }
    
}