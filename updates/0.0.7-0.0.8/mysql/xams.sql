ALTER TABLE `pm_customer_info_fields`
    ADD `ACL_Customer` SET('r','w') NOT NULL AFTER `ACL_Reseller`;

ALTER TABLE `pm_reseller_info_fields`
    ADD `ACL_Reseller` SET('r','w') NOT NULL AFTER `LdapName`;

