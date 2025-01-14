;; publish.el --- Publish org-mode project on Gitlab Pages
;; Author: Rasmus

;;; Commentary:
;; This script will convert the org-mode files in this directory into
;; html.

;;; Code:

(defvar site-attachments
  (regexp-opt '("jpg" "jpeg" "gif" "png" "svg"
                "ico" "cur" "css" "js" "woff" "html" "pdf"))
  "File types that are published as static fileTest 11s.")

(setq org-publish-project-alist
      (list
       (list "org"
             :base-directory "/tmp/doc/"
             :base-extension "org"
             :recursive t
             :publishing-function '(org-html-publish-to-html)
             :publishing-directory "/tmp/progression/app/html/doc/"
             :exclude (regexp-opt '("README" "draft" "démo" "thème"))
             :auto-sitemap t
             :sitemap-filename "index.org"
             :sitemap-file-entry-format "%d *%t*"
             :html-head-extra "<link rel=\"icon\" type=\"image/x-icon\" href=\"/favicon.ico\"/>"
             :sitemap-style 'list
			 )
       (list "démo"
             :base-directory "/tmp/doc/contenu/démo"
             :base-extension 'any
             :publishing-directory "/tmp/progression/app/html/doc/contenu/démo/"
             :publishing-function 'org-publish-attachment
             :recursive t)
       (list "thème"
             :base-directory "/tmp/doc/src"
             :base-extension 'any
             :publishing-directory "/tmp/progression/app/html/doc/src/"
             :publishing-function 'org-publish-attachment
             :recursive t)
       (list "images"
             :base-directory "/tmp/doc/images"
             :base-extension (regexp-opt '("jpg" "jpeg" "gif" "png" "svg" "ico"))
             :publishing-directory "/tmp/progression/app/html/doc/images"
             :publishing-function 'org-publish-attachment
             :recursive t)
 ))

(provide 'publish)
;;; publish.el ends here
