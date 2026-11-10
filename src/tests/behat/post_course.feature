@local @local_chuspasocial @javascript
Feature: Publish a post on a course wall
  In order to share information with my classmates
  As an enrolled student
  I want to publish a post on my course wall

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email                |
      | student1 | Student   | One      | student1@example.com |
    And the following "courses" exist:
      | fullname | shortname | category |
      | Curso 1  | CURSO1    | 0        |
    And the following "course enrolments" exist:
      | user     | course | role    |
      | student1 | CURSO1 | student |
    And the following "permission overrides" exist:
      | capability             | permission | role    | contextlevel | reference |
      | local/chuspasocial:post | Allow      | student | Course       | CURSO1    |

  Scenario: An enrolled student publishes a post on the course wall
    Given I log in as "student1"
    And I am on "Curso 1" course homepage
    When I follow "Wall"
    And I set the field "Post content" to "Publicacion de prueba para Curso 1"
    And I press "Publish"
    Then I should see "Publicacion de prueba para Curso 1"