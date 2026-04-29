@theme @theme_boost_union @theme_boost_union_smartmenusettings @theme_boost_union_smartmenusettings_menuitems @theme_boost_union_smartmenusettings_menuitems_divider
Feature: Configuring the theme_boost_union plugin on the "Smart menus" page, using a divider link item
  In order to use the features
  As admin
  I need to be able to configure the theme Boost Union plugin

  Background:
    Given the following "theme_boost_union > smart menu" exists:
      | title    | Quick links                                      |
      | location | Main navigation, Menu bar, User menu, Bottom bar |
    And the following "theme_boost_union > smart menu item" exists:
      | menu     | Quick links |
      | itemtype | Divider     |

  Scenario: Smartmenus: Menu items: Divider - Use a divider in smart menu
    When I log in as "admin"
    # Divider in main navigation.
    Then ".dropdown-divider" "css_element" should exist in the ".primary-navigation" "css_element"
    # Divider in user menu.
    And ".dropdown-divider" "css_element" should exist in the "#usermenu-carousel" "css_element"
    # Divider in bottom menu.
    And ".dropdown-divider" "css_element" should exist in the ".bottom-navigation" "css_element"
    # Divider in menubar.
    And ".dropdown-divider" "css_element" should exist in the "nav.menubar" "css_element"

  @javascript
  Scenario: Smartmenus: Menu items: Divider - Apply a custom CSS class to a divider item
    Given the following "theme_boost_union > smart menu item" exists:
      | menu     | Quick links      |
      | itemtype | Divider          |
      | cssclass | my-divider-class |
    When I log in as "admin"
    # Custom class on divider in main navigation.
    And I click on "Quick links" "link" in the ".primary-navigation" "css_element"
    Then ".primary-navigation .dropdown-divider.my-divider-class" "css_element" should exist
    # Custom class on divider in menubar.
    And I click on "Quick links" "link" in the ".boost-union-menubar" "css_element"
    Then ".boost-union-menubar .dropdown-divider.my-divider-class" "css_element" should exist
    # Custom class on divider in user menu.
    And I click on "#user-menu-toggle" "css_element"
    And I click on "Quick links" "link" in the "#usermenu-carousel" "css_element"
    Then ".dropdown-divider.my-divider-class" "css_element" should exist in the "#usermenu-carousel" "css_element"
    # Custom class on divider in bottom bar.
    And I change viewport size to "740x900"
    And I click on "Quick links" "link" in the ".bottom-navigation" "css_element"
    Then ".dropdown-divider.my-divider-class" "css_element" should exist in the ".bottom-navigation" "css_element"
