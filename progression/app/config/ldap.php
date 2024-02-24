<?
return [
  'domaine' => env('LDAP_DOMAINE'),
  'url_mdp_reinit' => env("URL_MDP_REINIT"),
  'hôte' => env("LDAP_HOTE"),
  'port' => env("LDAP_PORT"),
  'base' => env("LDAP_BASE"),
  'uid' => env("LDAP_UID"),
  'bind' => [
      'dn' => env("LDAP_DN_BIND"),
      'pw' => env("LDAP_PW_BIND"),
  ],
];
