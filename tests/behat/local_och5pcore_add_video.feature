@local @local_och5pcore
Feature: Add Opencast Video into H5P Core
  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email                | idnumber |
      | teacher1 | Teacher   | 1        | teacher1@example.com | T1       |
      | student1 | Student   | 1        | student1@example.com | S1       |
    And the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C1        | 0        |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | C1     | editingteacher |
      | student1 | C1     | student        |
    And the following config values are set as admin:
      | config              | value                                                         | plugin          |
      | apiurl_1            | https://stable.opencast.org                                   | tool_opencast   |
      | apiusername_1       | admin                                                         | tool_opencast   |
      | apipassword_1       | opencast                                                      | tool_opencast   |
      | ocinstances         | [{"id":1,"name":"Default","isvisible":true,"isdefault":true}] | tool_opencast   |
      | lticonsumerkey      | CONSUMERKEY                                                   | local_och5pcore |
      | lticonsumersecret   | CONSUMERSECRET                                                | local_och5pcore |
    And I log in as "admin"
    And I setup the opencast video block for the course with och5pcore
    And I get the latest h5p content types
    And I navigate to "Plugins > Local plugins > H5P Opencast Extension (Core)" in site administration
    Then I should see "Boost"
    And I set the following fields to these values:
      | Available themes to extend  | Boost           |
    And I press "Save changes"
    Then I should see "Changes saved"
    # Content bank accessibility is different in Moodle version < 4.0, therefore, we need a unified way "via Navigation block"
    And the following config values are set as admin:
      | unaddableblocks | | theme_boost|
    And I am on site homepage
    And I turn editing mode on
    And I add the "Navigation" block if not present
    And I configure the "Navigation" block
    And I set the following fields to these values:
      | Page contexts | Display throughout the entire site |
    And I press "Save changes"
    And I am on "Course 1" course homepage with editing mode on
    And I configure the "Navigation" block
    And I set the following fields to these values:
      | Display on page types | Any type of course main page |
    And I press "Save changes"
    And I log out

  @javascript @_switch_iframe
  Scenario: Teacher should be able to add and edit Opencast Video in H5P Interactive Videos, student should be able to see the video
    Given I log in as "teacher1"
    # We need to increase the size of the window in order for the h5p iframe contents to be visible and in the view port, otherwise
    # it won't see some of the flags and texts to validate.
    And I change window size to "1366x968"
    And I am on "Course 1" course homepage with editing mode on
    And I add the "Navigation" block if not present
    And I add the "Opencast Videos" block
    And I expand "Site pages" node
    And I click on "Content bank" "link"
    Then I should see "Add"
    When I click on "Add" "button"
    And I click on "Interactive Video" "link"
    Then I should see "Adding a new H5P interactive content"
    And I scroll to "iframe.h5p-editor-iframe" in och5pcore
    And I switch to "h5p-editor-iframe" class iframe
    And I click on ".shepherd-cancel-link" "css_element"
    And I set the field "Title" to "Test Opencast Video"
    And I scroll to "div.h5p-add-file" in och5pcore
    When I click on ".h5p-add-file[title='Add file']" "css_element"
    Then I should see "Opencast Videos"
    And I set the field "Select a video file" to "Spring"
    And I set the field "Select the video's flavor and quality" to "Presentation (mp4)"
    And I switch to the main frame
    When I click on "Save" "button"
    And I wait until the page is ready
    Then I should see "Test Opencast Video"
    And I should see "Edit"
    And I switch to "h5p-player" class iframe
    And I switch to "h5p-iframe" class iframe
    And I should see "Interactive Video"
    And I switch to the main frame
    When I click on "Edit" "link"
    Then I should see "Test Opencast Video"
    And I scroll to "iframe.h5p-editor-iframe" in och5pcore
    And I switch to "h5p-editor-iframe" class iframe
    And I set the field "Title" to "Test Opencast Video Edited"
    And I wait "2" seconds
    And I scroll to ".h5p-av-row .h5p-remove" in och5pcore
    When I click on ".h5p-av-row .h5p-remove" "css_element"
    And I should see "Remove file"
    And I click on "Confirm" "button"
    When I click on ".h5p-add-file[title='Add file']" "css_element"
    Then I should see "Opencast Videos"
    And I set the field "Select a video file" to "Spring"
    And I set the field "Select the video's flavor and quality" to "Presentation (mp4)"
    And I switch to the main frame
    When I click on "Save" "button"
    And I wait until the page is ready
    Then I should see "Test Opencast Video"
    And I should see "Edit"
    And I switch to "h5p-player" class iframe
    And I switch to "h5p-iframe" class iframe
    And I should see "Interactive Video"
    And I switch to the main frame
    And I am on "Course 1" course homepage
    And I add a "H5P" to section "1"
    Then I set the field "Name" to "H5P with Opencast Video"
    And I click on "Add..." "button" in the "Package file" "form_row"
    And I select "Content bank" repository in file picker
    And I click on "Test Opencast Video" "file" in repository content area
    And I click on "Link to the file" "radio"
    And I click on "Select this file" "button"
    And I click on "Save and display" "button"
    And I wait until the page is ready
    And I wait "3" seconds
    And I switch to "h5p-player" class iframe
    And I switch to "h5p-iframe" class iframe
    And I should see "Interactive Video"
    And I switch to the main frame
    And I log out
    Given I log in as "student1"
    And I am on "Course 1" course homepage
    Then I should see "H5P with Opencast Video"
    And I click on "li.h5pactivity a.aalink" "css_element"
    And I wait until the page is ready
    And I switch to "h5p-player" class iframe
    And I switch to "h5p-iframe" class iframe
    And I should see "Interactive Video"
