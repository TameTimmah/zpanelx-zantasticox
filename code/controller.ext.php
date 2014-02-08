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
    
    static function getMainView() {
        global $zdbh;
            $sql = "SELECT * FROM x_ai_categories";
            if ($uid == 0) {
                $sql = $zdbh->prepare($sql);
            }else{
                $sql = $zdbh->prepare($sql);
                $sql->bindParam(':uid', $uid);
            }
            $res = array();
            $sql->execute();
            while ($rowdomains = $sql->fetch()) {
                array_push($res, array(
                    'ai_name' => $rowdomains['ai_name'],
                    'ai_desc' => $rowdomains['ai_desc'],
                    'directory' => $rowdomains['vh_directory_vc'],
                    'active' => $rowdomains['vh_active_in'],
                    'id' => $rowdomains['vh_id_pk'],
                ));
                $html .= '<h3>'.$rowdomains['ai_name'].'</h3>';
                $html .= '<p>'.$rowdomains['ai_desc'].'</p>';
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
            $html = '<h1>Unknown Error Occurred</h1>';
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
        
        <% if MainView %>
        <style>
            #app_mainview a {
                display: inline-block;
                text-align: center;
                width:100px;
                margin:5px;
                padding:10px;
                background-color:#f5f5f5;
                border-radius: 4px;
                border: 1px solid #DDD;
            }
            #app_mainview a h5 {
                margin-bottom:0;
            }
            #app_mainview a h6 {
                color:#666;
                display:inline-block;
                margin:0;
            }
        </style>
        <div id="app_mainview">
            <% loop Sections %>
            <section>
                <h3><~ section_name ~></h3>
                <p><~ section_desc ~></p>
                <% loop Apps %>
                <a href="?module=app_installer&app=<~ app_folder ~>">
                    <img src="modules/app_installer/apps/<~ app_folder ~>/smallicon.png" width="50" height="50" alt="<~ app_name ~>">
                    <h5><~ app_name ~></h5>
                    <h6><~ app_type ~></h6>
                </a>
                <% endloop %>
            </section>
            <% endloop %>
        </div>
        <% endif %>
     */
}