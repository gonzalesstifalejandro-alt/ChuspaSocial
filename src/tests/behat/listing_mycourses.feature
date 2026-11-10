@local @local_chuspasocial @javascript
Feature: View only the books of my courses
  In order to find books relevant to my studies
  As a student
  I need to see only the book listings linked to the courses I am enrolled in

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email                |
      | seller1  | Seller    | One      | seller1@example.com  |
      | student1 | Student   | One      | student1@example.com |
    And the following "courses" exist:
      | fullname | shortname |
      | Course 1 | C1        |
      | Course 2 | C2        |
    And the following "course enrolments" exist:
      | user     | course | role    |
      | seller1  | C1     | student |
      | seller1  | C2     | student |
      | student1 | C1     | student |
    And the following config values are set as admin:
      | config            | value | plugin             |
      | enablemarketplace | 1     | local_chuspasocial |
    And I log in as "seller1"
    And I visit "/local/chuspasocial/listing_edit.php"
    And I set the following fields to these values:
      | title   | Book for Course 1 |
      | author  | Author One        |
      | price   | 10                |
      | Courses | Course 1          |
    And I press "Save changes"
    And I visit "/local/chuspasocial/listing_edit.php"
    And I set the following fields to these values:
      | title   | Book for Course 2 |
      | author  | Author Two        |
      | price   | 20                |
      | Courses | Course 2          |
    And I press "Save changes"
    And I log out

  Scenario: A student sees only the books linked to their own courses
    Given I log in as "student1"
    When I visit "/local/chuspasocial/marketplace.php"
    Then I should see "Book for Course 1"
    And I should not see "Book for Course 2"