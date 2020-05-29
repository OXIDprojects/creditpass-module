<?php

class oecreditPass_GuiElementsTest extends oxTestCase
{
    /**
     * testing newsletter activation in admin
     *
     * @group oecreditpass
     */
    public function testIfModuleOptionsAreAvailableInAdmin()
    {
        $this->loginAdminForModule("Shop Settings", "creditPass");
        $this->openTab("Settings");
        $this->assertElementPresent("//input[@name='confbools[blOECreditPassIsActive]' and @value='false']");
        $this->assertElementPresent("//input[@name='confstrs[sOECreditPassUrl]' and @value='https://secure.creditpass.de/atgw/authorize.cfm']");
        $this->assertElementPresent("//input[@name='confstrs[sOECreditPassAuthId]' and @value='']");
        $this->assertElementPresent("//input[@name='confstrs[sOECreditPassAuthPw]' and @value='']");
        $this->assertElementPresent("confarrs[aOECreditPassExclUserGroups][]");
        $this->assertElementPresent("//input[@name='iOECreditPassCheckCacheTimeout' and @value='0']");
        $this->assertElementPresent("//select[@name='confstrs[iOECreditPassManualWorkflow]']");
        $this->assertElementPresent("sUnauthorizedErrorMsg[oxcontents__oxcontent]");
        $this->assertElementPresent("sUnauthorizedErrorMsg[oxcontents__oxcontent_1]");
        $this->assertElementPresent("//input[@name='confbools[blOECreditPassIsActive]' and @value='false']");
        $this->assertElementPresent("//input[@name='confbools[blOECreditPassDebug]' and @value='false']");
    }
}
