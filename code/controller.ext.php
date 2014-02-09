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
    
    // Main View (List of Apps)
    static function getMainView() {
        global $zdbh;
        
        // Get categories
        $query = "SELECT * FROM x_ai_categories";
        $sql = $zdbh->prepare($query);
        $sql->execute();
        
        // For every category
        while ($rowdomains = $sql->fetch()) {
            $html .= '<section class="mainview">';
            $html .= '<h3>'.$rowdomains['ai_name'].'</h3>';
            $html .= '<p>'.$rowdomains['ai_desc'].'</p>';
            
            // Get apps in category
            $types = unserialize(base64_decode($rowdomains['ai_types']));
            $ids = "'". implode("', '", $types) ."'";
            $query2 = "SELECT * FROM x_ai_apps WHERE ai_type IN ($ids)";
            $sql2 = $zdbh->prepare($query2);
            $sql2->execute();
            
            // For every app in category
            while ($rowdomains2 = $sql2->fetch()) {
                $foldername = strtolower($rowdomains2['ai_name']);
                $html .= '<a href="?module=app_installer&app='.$foldername.'">
                    <img src="modules/app_installer/apps/'.$foldername.'/smallicon.png" width="50" height="50" alt="'.$rowdomains2['ai_name'].'">
                    <h5>'.$rowdomains2['ai_name'].'</h5>
                    <h6>'.$rowdomains2['ai_type'].'</h6>
                </a>';
            }
            $html .= '</section">';
        }
        return $html;
    }
    
    static function getAppView($id) {
        if($id==1){
            $html = '<h1>App View!</h1>';
        }
        return $html;
    }
    
    static function getAppInstall($id) {
        if($id==1){
            $html = '<h1>App Installer View!</h1>';
        }
        return $html;
    }
    
    // Handles frontend
    static function getModuleDisplay() {
        // Add GET contents to variables
        $app = $_GET['app'];
        $action = $_GET['action'];
        
        // Decide what should be displayed
        if($app==NULL & $action==NULL){
            $html = module_controller::getMainView();
        }
        elseif($app!=NULL & $action==NULL){
            $html = module_controller::getAppView($app);
        }
        elseif($app!=NULL & $action=='install'){
            $html = module_controller::getAppInstall($app);
        }
        else{
            header("HTTP/1.0 404 Not Found");
            $html = '<h1>404 Not Found</h1>';
        }
        return $html;
    }
    
    /*
     * OLD SAMPLE CODE (for zpm frontend)
     * You can safely delete this
     * 
        <% if TopBar %>
        <style>
            #app_topbar form.form-inline input{
                width:250px;
            }

            #app_topbar {
                min-height:30px;
            }

            @media (max-width: 767px) {
                
                #app_topbar select {
                    margin-bottom: 15px;
                }
                
                #app_topbar .pull-left, #app_topbar .pull-right {
                    float:none;
                }
                
                #app_topbar form.form-inline input{
                    width:100%;
                    max-width:250px;
                }
                
            }
        </style>
        <div id="app_topbar">
            <div class="pull-left">
                <select class="form-control">
                    <option><: All Applications :></option>
                    <option><: Community :></option>
                    <option><: Content Management :></option>
                    <option><: Business :></option>
                    <option><: Files :></option>
                    <option><: Surveys :></option>
                    <option><: Other :></option>
                </select>
            </div>
            <form class="pull-right form-inline" role="form">
                <div class="form-group">
                    <input type="text" class="form-control" id="appsearch" placeholder="<: Search Apps :>">
                    <button type="submit" class="btn btn-default"><: Search :></button>
                </div>
            </form>
        </div>
        <hr>
        <% endif %>
     */
}