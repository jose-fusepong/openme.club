{
  "slug":        "data",
  "properties":  {
    "slug":                  "data",
    "load_priority":         1,
    "name":                  "Data",
    "show_module_menu_item": false,
    "auto_enabled":          true,
    "storage_key":           "data",
    "show_central":          false,
    "premium":               false,
    "access_restricted":     true,
    "run_if_whitelisted":    true,
    "run_if_wpcli":          true,
    "skip_processor":        true,
    "tracking_exclude":      true
  },
  "wpcli":       {
    "enabled": false
  },
  "sections":    [
  ],
  "options":     [
  ],
  "definitions": {
    "db_handler_classes": {
      "ips":       "\\FernleafSystems\\Wordpress\\Plugin\\Shield\\Modules\\Data\\DB\\IPs\\Ops\\Handler",
      "req_logs":  "\\FernleafSystems\\Wordpress\\Plugin\\Shield\\Modules\\Data\\DB\\ReqLogs\\Ops\\Handler",
      "user_meta": "\\FernleafSystems\\Wordpress\\Plugin\\Shield\\Modules\\Data\\DB\\UserMeta\\Ops\\Handler"
    },
    "db_table_ips":       {
      "autoexpire":     0,
      "slug":           "ips",
      "has_updated_at": false,
      "has_deleted_at": false,
      "col_older_than": "created_at",
      "cols_custom":    {
        "ip":  {
          "macro_type": "ip",
          "attr":       [
            "UNIQUE"
          ]
        },
        "geo": {
          "macro_type": "meta",
          "comment":    "GeoIP Data"
        }
      }
    },
    "db_table_req_logs":  {
      "slug":           "req_logs",
      "autoexpire":     0,
      "has_updated_at": false,
      "has_deleted_at": false,
      "cols_custom":    {
        "req_id":  {
          "macro_type": "varchar",
          "length":     10,
          "attr":       [
            "UNIQUE"
          ]
        },
        "ip_ref":  {
          "macro_type":  "foreign_key_id",
          "foreign_key": {
            "ref_table": "icwp_wpsf_ips"
          }
        },
        "type":    {
          "macro_type": "char",
          "length":     1,
          "comment":    "Type of requests"
        },
        "verb":    {
          "macro_type": "varchar",
          "length":     8,
          "comment":    "Request Verb"
        },
        "path":    {
          "macro_type": "varchar",
          "length":     512,
          "comment":    "Request Path"
        },
        "code":    {
          "macro_type": "unsigned_int",
          "type":       "smallint",
          "length":     3,
          "default":    0,
          "comment":    "Response Code"
        },
        "uid":     {
          "macro_type": "bigint",
          "comment":    "User ID",
          "no_length":  true
        },
        "offense": {
          "macro_type": "bool",
          "default":    0,
          "comment":    "Was Offense"
        },
        "meta":    {
          "macro_type": "meta"
        }
      }
    },
    "db_table_user_meta": {
      "slug":            "user_meta",
      "autoexpire":      0,
      "has_updated_at":  true,
      "has_deleted_at":  false,
      "cols_custom":     {
        "user_id": {
          "macro_type": "unsigned_int",
          "type":       "bigint",
          "length":     11,
          "attr":       [
            "UNIQUE"
          ]
        },
        "ip_ref":  {
          "macro_type":  "foreign_key_id",
          "foreign_key": {
            "ref_table": "icwp_wpsf_ips"
          }
        }
      },
      "cols_timestamps": {
        "backupcode_ready_at":  {
          "comment": "Backup Code Ready"
        },
        "email_ready_at":       {
          "comment": "Email Ready"
        },
        "ga_ready_at":          {
          "comment": "Google Auth Ready"
        },
        "u2f_ready_at":         {
          "comment": "U2F Ready"
        },
        "yubi_ready_at":        {
          "comment": "Yubikey Ready"
        },
        "first_seen_at":        {
          "comment": "User First Seen"
        },
        "last_login_at":        {
          "comment": "Last Login At"
        },
        "last_2fa_verified_at": {
          "comment": "Last 2FA Verified"
        },
        "hard_suspended_at":    {
          "comment": "Hard Suspend"
        },
        "pass_started_at":      {
          "comment": "Password First Seen or Set"
        }
      }
    }
  }
}