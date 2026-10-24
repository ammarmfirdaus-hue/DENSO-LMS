import React from 'react';
import { Editor } from '@tinymce/tinymce-react';

const TINYMCE_API_KEY =
  import.meta.env.VITE_TINYMCE_LICENSE_KEY ||
  'ksyqj942fz74qh6oamlkvkh7d9che71f9e1lo89ephnd1loq';

/**
 * Editor TinyMCE berbasis Cloud dengan konfigurasi resmi Tiny Cloud (Option 2 & 3).
 * Menggunakan API key dan paket plugin lengkap (Core + Premium).
 *
 * @param {{
 *   value: string,
 *   onChange: (content: string) => void,
 *   height?: number
 * }} props
 */
export default function TinyEditor({ value, onChange, height = 400 }) {
  return (
    <div className="w-full overflow-hidden rounded-xl border border-outline/50 shadow-xs">
      <Editor
        apiKey={TINYMCE_API_KEY}
        value={value || ''}
        onEditorChange={(content) => onChange(content)}
        init={{
          height,
          menubar: false,
          plugins: [
            // Core editing features
            'anchor', 'autolink', 'charmap', 'codesample', 'emoticons', 'link', 'lists', 'media', 'searchreplace', 'table', 'visualblocks', 'wordcount',
            // Premium features
            'checklist', 'mediaembed', 'casechange', 'formatpainter', 'pageembed', 'a11ychecker', 'tinymcespellchecker', 'permanentpen', 'powerpaste', 'advtable', 'advcode', 'advtemplate', 'tinymceai', 'uploadcare', 'mentions', 'tinycomments', 'tableofcontents', 'footnotes', 'mergetags', 'autocorrect', 'typography', 'inlinecss', 'markdown', 'importword', 'exportword', 'exportpdf'
          ],
          toolbar: 'undo redo | tinymceai-chat tinymceai-quickactions tinymceai-review | blocks fontfamily fontsize | bold italic underline strikethrough | link media table mergetags | addcomment showcomments | spellcheckdialog a11ycheck typography uploadcare | align lineheight | checklist numlist bullist indent outdent | emoticons charmap | removeformat',
          tinycomments_mode: 'embedded',
          tinycomments_author: 'Author name',
          mergetags_list: [
            { value: 'First.Name', title: 'First Name' },
            { value: 'Email', title: 'Email' },
          ],
          tinymceai_token_provider: async () => {
            await fetch(`https://demo.api.tiny.cloud/1/${TINYMCE_API_KEY}/auth/random`, {
              method: 'POST',
              credentials: 'include',
            });
            return {
              token: await fetch(`https://demo.api.tiny.cloud/1/${TINYMCE_API_KEY}/jwt/tinymceai`, {
                credentials: 'include',
              }).then((r) => r.text()),
            };
          },
          uploadcare_public_key: 'c7cd2603a9017bfc74e8',
          content_style: [
            "body{font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;font-size:15px;color:#1e293b;line-height:1.7;padding:12px 16px}",
            'h1,h2,h3{color:#0f172a;font-weight:700;margin:1em 0 .4em}',
            'p{margin:.6em 0}a{color:#d71920}',
            'table{border-collapse:collapse;width:100%}',
            'td,th{border:1px solid #e2e8f0;padding:8px 12px}',
            'th{background:#f8fafc;font-weight:600}',
          ].join(' '),
        }}
      />
    </div>
  );
}
