<?php
/**
 * CANALI Luxury Tailoring — Contacts (Clients) EditView
 *
 * Adds three custom panels:
 *   • Body Measurements
 *   • Style Preferences
 *   • VIP Profile
 *
 * The assigned_user_name field (Personal Stylist / Tailor) is prominently
 * placed at the top of the core panel.
 */

if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

$viewdefs['Contacts']['EditView'] = array(
    'templateMeta' => array(
        'form' => array(
            'hidden' => array(
                '<input type="hidden" name="opportunity_id" value="{$smarty.request.opportunity_id}">',
                '<input type="hidden" name="case_id" value="{$smarty.request.case_id}">',
                '<input type="hidden" name="bug_id" value="{$smarty.request.bug_id}">',
                '<input type="hidden" name="email_id" value="{$smarty.request.email_id}">',
                '<input type="hidden" name="inbound_email_id" value="{$smarty.request.inbound_email_id}">',
            ),
        ),
        'maxColumns' => '2',
        'widths' => array(
            array('label' => '10', 'field' => '30'),
            array('label' => '10', 'field' => '30'),
        ),
        'useTabs' => false,
        'tabDefs' => array(
            'LBL_CONTACT_INFORMATION' => array('newTab' => false, 'panelDefault' => 'expanded'),
            'LBL_PANEL_ADVANCED'      => array('newTab' => false, 'panelDefault' => 'expanded'),
        ),
    ),
    'panels' => array(

        // ── Core Client Information ─────────────────────────────────────────
        'lbl_contact_information' => array(
            array(
                array(
                    'name' => 'first_name',
                    'customCode' => '{html_options name="salutation" id="salutation" options=$fields.salutation.options selected=$fields.salutation.value}&nbsp;<input name="first_name"  id="first_name" size="25" maxlength="25" type="text" value="{$fields.first_name.value}">',
                ),
                'last_name',
            ),
            array(
                array('name' => 'assigned_user_name', 'label' => 'LBL_ASSIGNED_TO_NAME'),
                array('name' => 'canali_client_tier_c', 'label' => 'LBL_CANALI_CLIENT_TIER'),
            ),
            array(
                array('name' => 'phone_work',   'label' => 'LBL_OFFICE_PHONE'),
                array('name' => 'phone_mobile', 'label' => 'LBL_MOBILE_PHONE'),
            ),
            array(
                array('name' => 'email1', 'studio' => 'false', 'label' => 'LBL_EMAIL_ADDRESS'),
                array('name' => 'birthdate'),
            ),
            array(
                array('name' => 'title', 'label' => 'LBL_TITLE'),
                array('name' => 'account_name',
                    'displayParams' => array(
                        'key' => 'billing', 'copy' => 'primary',
                        'billingKey' => 'primary',
                        'additionalFields' => array('phone_office' => 'phone_work'),
                    ),
                ),
            ),
            array(
                array(
                    'name' => 'primary_address_street',
                    'hideLabel' => true,
                    'type' => 'address',
                    'displayParams' => array('key' => 'primary', 'rows' => 2, 'cols' => 30, 'maxlength' => 150),
                ),
                array(
                    'name' => 'alt_address_street',
                    'hideLabel' => true,
                    'type' => 'address',
                    'displayParams' => array('key' => 'alt', 'copy' => 'primary', 'rows' => 2, 'cols' => 30, 'maxlength' => 150),
                ),
            ),
            array(
                array('name' => 'description', 'label' => 'LBL_DESCRIPTION'),
                '',
            ),
        ),

        // ── Body Measurements ───────────────────────────────────────────────
        'LBL_CANALI_MEASUREMENTS_PANEL' => array(
            array(
                array('name' => 'canali_chest_c',      'label' => 'LBL_CANALI_CHEST'),
                array('name' => 'canali_waist_c',       'label' => 'LBL_CANALI_WAIST'),
            ),
            array(
                array('name' => 'canali_hips_c',        'label' => 'LBL_CANALI_HIPS'),
                array('name' => 'canali_shoulders_c',   'label' => 'LBL_CANALI_SHOULDERS'),
            ),
            array(
                array('name' => 'canali_sleeve_c',      'label' => 'LBL_CANALI_SLEEVE'),
                array('name' => 'canali_inseam_c',      'label' => 'LBL_CANALI_INSEAM'),
            ),
            array(
                array('name' => 'canali_neck_c',        'label' => 'LBL_CANALI_NECK'),
                array('name' => 'canali_jacket_size_c', 'label' => 'LBL_CANALI_JACKET_SIZE'),
            ),
            array(
                array('name' => 'canali_trouser_size_c',  'label' => 'LBL_CANALI_TROUSER_SIZE'),
                array('name' => 'canali_shirt_collar_c',  'label' => 'LBL_CANALI_SHIRT_COLLAR'),
            ),
            array(
                array('name' => 'canali_last_measured_c', 'label' => 'LBL_CANALI_LAST_MEASURED'),
                '',
            ),
            array(
                array('name' => 'canali_measurement_notes_c', 'label' => 'LBL_CANALI_MEASUREMENT_NOTES'),
                '',
            ),
        ),

        // ── Style Preferences ───────────────────────────────────────────────
        'LBL_CANALI_STYLE_PANEL' => array(
            array(
                array('name' => 'canali_fit_style_c',        'label' => 'LBL_CANALI_FIT_STYLE'),
                array('name' => 'canali_preferred_fabric_c', 'label' => 'LBL_CANALI_PREFERRED_FABRIC'),
            ),
            array(
                array('name' => 'canali_preferred_colors_c', 'label' => 'LBL_CANALI_PREFERRED_COLORS'),
                array('name' => 'canali_monogram_c',         'label' => 'LBL_CANALI_MONOGRAM'),
            ),
            array(
                array('name' => 'canali_lining_pref_c',  'label' => 'LBL_CANALI_LINING_PREF'),
                array('name' => 'canali_button_pref_c',  'label' => 'LBL_CANALI_BUTTON_PREF'),
            ),
            array(
                array('name' => 'canali_style_notes_c', 'label' => 'LBL_CANALI_STYLE_NOTES'),
                '',
            ),
        ),

        // ── VIP Profile ─────────────────────────────────────────────────────
        'LBL_CANALI_VIP_PANEL' => array(
            array(
                array('name' => 'canali_client_since_c',   'label' => 'LBL_CANALI_CLIENT_SINCE'),
                array('name' => 'canali_annual_spend_c',   'label' => 'LBL_CANALI_ANNUAL_SPEND'),
            ),
            array(
                array('name' => 'canali_lifetime_value_c', 'label' => 'LBL_CANALI_LIFETIME_VALUE'),
                array('name' => 'lead_source',             'label' => 'LBL_LEAD_SOURCE'),
            ),
            array(
                array('name' => 'canali_special_occasions_c', 'label' => 'LBL_CANALI_SPECIAL_OCCASIONS'),
                '',
            ),
            array(
                array('name' => 'canali_private_notes_c', 'label' => 'LBL_CANALI_PRIVATE_NOTES'),
                '',
            ),
        ),

    ),
);
