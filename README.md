moodle-local_och5pcore
=====================
This local plugin helps to integrate Opencast Video into the Moodle H5P core.
The main purpose of this plugin is to make it possible for the teachers to select Opencast Video from within the H5P Editor when using H5P Interactive Videos feature.
In order to achieve this goal, it is necessary to customize Moodle H5P core, which is only possible through extending a theme in Moodle <a href="https://h5p.org/moodle-customization">Moodle Customization</a>. From Moodle 3.10 onwards the H5P Core has the ability to alter H5P via overriding the renderes and hooks <a href="https://tracker.moodle.org/browse/MDL-69087">MDL-69087</a> to add customized scripts and styles into H5P.
This plugin is designed to overwrite the renderer.php and config.php files of the selected themes and append the necessary codes into these files. This design helps to adapt every installed themes instead of only extending a specific theme.
Using this integration now enables teachers to select opencast videos in a course, using a dropdown inside the H5P Interactive Videos' editor in course content bank. After selecting the opencast video, another dropdown will be shown to select different types of video flavor (Presenter/Presentation). By selecting the video flavor all available qualities of the video then will be inserted into H5P Editor videos list and the rest will be processed by H5P.

System requirements
------------------
1. Min. Moodle Version: 3.10
2. Installed plugin:
   - <a href="https://github.com/Opencast-Moodle/moodle-tool_opencast">tool_opencast</a> (Min. version: <a href="https://github.com/Opencast-Moodle/moodle-tool_opencast/releases/tag/v3.11-r3">v3.11-r3</a>)
   - <a href="https://github.com/Opencast-Moodle/moodle-block_opencast">block_opencast</a> (Min. version: <a href="https://github.com/Opencast-Moodle/moodle-block_opencast/releases/tag/v3.11-r3">v3.11-r3</a>)
   - IMPORTANT: You should update the tool_opencast first because otherwise the block_opencast installation will fail.

Prerequisites
------------------
* Proper write permission on themes directories for the server user (e.g. "www-data" Apache User)
* In case you are using the unofficial version (tool_och5p_core) v1.0.0, it is recommended to uninstall it.

Features
------------------
* Extend/Remove extensions of several themes at once
* Display Opencast videos of the course inside H5P Interactive Videos Editor
* Extract and display Opencast video flavors inside H5P Interactive Videos Editor
* Extract and use different quality of the Opencast video inside H5P Interactive Videos
* Opencast LTI authentication (v2.0.0)
* Getting search endpoint (Engage/Presentation node) from Opencast services (v2.1)

Settings
------------------
* In Admin Settings Page, there is the possibility to select multiple available themes to extend.
* Unselecting a theme will remove the extension changes.
* Only videos which are published to opencast engage player, can be displayed and process, because media index of the event must be available.
* Opencast instance for the search endpoint must be configured in tool_opencast and be selected in the setting. (Deprecated in v2.1)
* LTI credential can be configured if the "Secure Static Files" in opencast setting is enabled.

Uninstall
------------------
In case the plugin triggers the uninstall event, all changes to the extended themes will be removed!
