<?php

require_once '../../../../oc-load.php';

class Frontend_users extends FrontendTest {

    /*
     * Register a user without email validation.
     */
    function testUsers_AddNewUser()
    {
        // same as Frontend-register.php function testRegisterNewUser_NoValidation()
        $uSettings = new utilSettings();

        $old_enabled_users           = $uSettings->set_enabled_users(1);
        $old_enabled_users_registration = $uSettings->set_enabled_user_registration(1);
        $old_enabled_user_validation = $uSettings->set_enabled_user_validation(0);

        $this->doRegisterUser();
        $this->assertTrue( $this->selenium->isTextPresent('Your account has been created successfully'), 'Register new user without validation.');

        $uSettings->set_enabled_users($old_enabled_users);
        $uSettings->set_enabled_user_registration($old_enabled_users_registration);
        $uSettings->set_enabled_user_validation($old_enabled_user_validation);

        unset($uSettings);
    }

    /*
     * Login user.
     * Change the password:
     *  - Incorrect current password.
     *  - Empty passwords.
     *  - Passwords do not match.
     * Logout user
     */
    function testUsers_ChangePassword()
    {
        $this->loginWith();
        $this->assertTrue($this->selenium->isTextPresent("User account manager"), 'Login at website.');

        $this->selenium->click("xpath=//ul/li/a[text()='My account']");
        $this->selenium->waitForPageToLoad("30000");

        $this->selenium->click("link=Modify password");
        $this->selenium->waitForPageToLoad("3000");

        // test - current password don't match
        $this->selenium->type("password"        , "qwerty");
        $this->selenium->type("new_password"    , $this->_password);
        $this->selenium->type("new_password2"   , $this->_password);
        $this->selenium->click("//button[@type='submit']");
        $this->selenium->waitForPageToLoad("3000");
        $this->assertTrue( $this->selenium->isTextPresent("Current password doesn't match"), "User, change the user password.");

        // test - Passwords can't be empty
        $this->selenium->type("password"        , $this->_password);
        $this->selenium->type("new_password"    , '');
        $this->selenium->type("new_password2"   , $this->_password);
        $this->selenium->click("//button[@type='submit']");
        $this->selenium->waitForPageToLoad("3000");
        $this->assertTrue( $this->selenium->isTextPresent("Password cannot be blank"), "User, change the user password, one blank password field.");

        // test - Passwords don't match
        $this->selenium->type("password"        , $this->_password);
        $this->selenium->type("new_password"    , 'abc');
        $this->selenium->type("new_password2"   , 'def');
        $this->selenium->click("//button[@type='submit']");
        $this->selenium->waitForPageToLoad("3000");
        $this->assertTrue( $this->selenium->isTextPresent("Passwords don't match"), "User, change the user password, passwords don't match.");

        $this->logout();
    }

    /*
     * Registrer user2 without validation email
     * Login user1
     * Change email:
     *  - The specified e-mail is already in use.
     *  - Change email correctly.
     * Logout
     * Remove user2
     */
    function testUser_ChangeEmail()
    {
        $uSettings = new utilSettings();

        $old_enabled_users           = $uSettings->set_enabled_users(1);
        $old_enabled_users_registration = $uSettings->set_enabled_user_registration(1);
        $old_enabled_user_validation = $uSettings->set_enabled_user_validation(0);
        
        // add another user
        $this->doRegisterUser('foo@bar.com', 'password');

        $this->loginWith();
        $this->assertTrue($this->selenium->isTextPresent("User account manager"), 'Login at website.');
        
        $this->selenium->click("xpath=//ul/li/a[text()='My account']");
        $this->selenium->waitForPageToLoad("30000");

        $this->selenium->click("link=Modify e-mail");
        $this->selenium->waitForPageToLoad("30000");
        
        // test - The specified e-mail is already in use
        $this->selenium->type("email"     , $this->_email);
        $this->selenium->type("new_email" , 'foo@bar.com');
        
        $this->selenium->click("//button[@type='submit']");
        $this->selenium->waitForPageToLoad("30000");

        $this->assertTrue( $this->selenium->isTextPresent("The specified e-mail is already in use"), "Change user email, for an existent user email.");
        
        /*
         *   ------------     Force validation !  =>  enabled_user_validation()   ------------------
         */
        $uSettings->set_enabled_user_validation(1);
        // with validation
        $this->selenium->click("xpath=//ul/li/a[text()='My account']");
        $this->selenium->waitForPageToLoad("3000");

        $this->selenium->click("link=Modify e-mail");
        $this->selenium->waitForPageToLoad("3000");

        $this->selenium->type("email"     , $this->_email);
        $this->selenium->type("new_email" , "test@test.com");
        $this->selenium->click("//button[@type='submit']");
        $this->selenium->waitForPageToLoad("3000");        

        $this->assertTrue( $this->selenium->isTextPresent("We have sent you an e-mail. Follow the instructions to validate the changes"), "Change user email, with email validation.");

        $this->logout();
        
        $this->removeUserByMail('foo@bar.com');

        $uSettings->set_enabled_users($old_enabled_users);
        $uSettings->set_enabled_user_registration($old_enabled_users_registration);
        $uSettings->set_enabled_user_validation($old_enabled_user_validation);

        unset($uSettings);
    }

    /*
     * Remove user1
     */
    function testUser_RemoveNewUser()
    {
        $this->removeUserByMail($this->_email);
    }
}

?>