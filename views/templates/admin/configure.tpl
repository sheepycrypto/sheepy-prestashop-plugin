{**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License version 3.0
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 *}

<div class="alert alert-info" style="overflow: hidden;">
    <div style="float: left; margin-right: 15px;">
        <img src="{$modulePath|escape:'html':'UTF-8'}logo.png" style="width: 40px; height: auto;">
    </div>
    <div>
        <b>{l s="Accept Cryptocurrency Payments with Sheepy - The Leading Payment Gateway" d="Modules.Sheepy.Admin"}</b><br>
        {l s="We offer reliable payment gateway to start accepting BTC, LTC, BCH, ETH, USDT, TRX and other digital assets right away." d="Modules.Sheepy.Admin"}

    </div>
</div>

<prestashop-accounts></prestashop-accounts>
<br>
<div id="prestashop-cloudsync"></div>
<div id="ps-billing"></div>
<br>
<div id="ps-modal"></div>

<script src="{$urlAccountsCdn|escape:'htmlall':'UTF-8'}" rel=preload></script>
<script src="{$urlCloudsync|escape:'htmlall':'UTF-8'}"></script>
<script src="{$urlBilling|escape:'htmlall':'UTF-8'}" rel=preload></script>

<script>
    window?.psaccountsVue?.init();

    const cdc = window.cloudSyncSharingConsent;

    cdc.init('#prestashop-cloudsync');

    cdc.on('OnboardingCompleted', (isCompleted) => {
        console.log('OnboardingCompleted', isCompleted);
    });

    cdc.isOnboardingCompleted((isCompleted) => {
        console.log('Onboarding is already Completed', isCompleted);
    });

    window.psBilling.initialize(window.psBillingContext.context, '#ps-billing', '#ps-modal', (type, data) => {
		// Event hook listener
		switch (type) {
		  case window.psBilling.EVENT_HOOK_TYPE.BILLING_INITIALIZED:
		    console.log('Billing initialized', data);
		    break;
		  case window.psBilling.EVENT_HOOK_TYPE.SUBSCRIPTION_UPDATED:
		    console.log('Sub updated', data);
		    break;
		  case window.psBilling.EVENT_HOOK_TYPE.SUBSCRIPTION_CANCELLED:
		    console.log('Sub cancelled', data);
		    break;
		}
	});
</script>
