moodle-local_och5pcore
=====================
The main purpose of this plugin is to make it possible for the teachers to select Opencast Video from within the H5P Editor when using H5P Interactive Videos feature.<br />
It provides an easy way to add support for H5P opencast to your site themes. It does this by writing new code into your theme renderer.php and config.php files, adding the code required to allow your theme to render H5P opencast content (as suggested by H5P.org in <a href="https://h5p.org/moodle-customization">Moodle Customization</a>). Your web server process must also have write access to the Moodle installation tree for this plugin to function.<br />
This design helps to apply the required extension to every installed theme dynamically, instead of creating an extended theme!
Using this integration now enables teachers to select opencast videos in a course, using a dropdown inside the H5P Interactive Videos' editor in course content bank. After selecting the opencast video, another dropdown will be shown to select different types of video flavor (Presenter/Presentation). By selecting the video flavor, all available qualities of the video then will be inserted into H5P Editor videos list and the rest will be processed by H5P.


System requirements
------------------
1. Min. Moodle Version: 3.10: <br />From Moodle 3.10 onwards, the H5P Core has the ability to alter H5P via overriding the renderers and hooks <a href="https://tracker.moodle.org/browse/MDL-69087">MDL-69087</a> to add customized scripts and styles into H5P.
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
* Extend several themes at once via Moodle's multiselect feature by holding the Ctrl key.
* Remove extensions applied to several themes at once via Moodle's multiselect feature by holding the Ctrl key.
* Display Opencast videos of the course inside H5P Interactive Videos Editor.
* Extract and display Opencast video flavors inside H5P Interactive Videos Editor.
* Extract and use different quality of the Opencast video inside H5P Interactive Videos.
* Opencast LTI authentication
* Getting search endpoint (Engage/Presentation node) from Opencast services

How it works
------------------
* In the admin setting page, there is the possibility to select multiple available themes to extend.
* Deselecting a theme will remove the extension changes.
* Only videos which are published to opencast engage player, can be displayed and process, because media index of the event must be available.
* LTI credential can be configured if the "Secure Static Files" in opencast setting is enabled.

Important for admins to know:
------------------
* This plugin creates new files within the Moodle core installation.
* By extending a theme, the plugin attempts to add own code into the files of selected themes.

How to revert the changes:
------------------
* Through the admin setting page, deselecting a theme will revert the changes.
* Uninstalling the plugin will also trigger the uninstallation event, by which all changes to the extended themes will be removed!

Revert changes manually:
------------------
It is possible to revert the changes manually, but it is not recommended doing so. However, the plugin only changes the files as follows:
* (rootdir) > themes > {your installed theme dir} > renderers.php
* (rootdir) > themes > {your installed theme dir} > config.php
Changes made by this plugin can be identified as a code block started with a comment containing "// Added by local_och5pcore plugin" and ends with a comment containing "// End of local_och5pcore code block."

Repair the loss of changes on renderers.php:
----------------
In case the changes on renderers.php or even the file itself is gone, the plugin will repeat the changes by itself which can be done simply via admin setting page:

1. Deselect the defected theme, to let the plugin know that the changes should not be there anymore!
2. Save changes.
3. Select the defected theme again, to repeat the changes.
4. Save changes.
