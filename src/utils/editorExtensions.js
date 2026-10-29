import { Extension, Node } from "@tiptap/core";
import { Plugin } from "@tiptap/pm/state";
import StarterKit from "@tiptap/starter-kit";
import { TableKit } from "@tiptap/extension-table";
import Image from "@tiptap/extension-image";
import Subscript from "@tiptap/extension-subscript";
import Superscript from "@tiptap/extension-superscript";

const supportedTags = new Set(
  "p br h2 h3 h4 h5 h6 strong em b i u s blockquote pre code ul ol li table thead tbody tfoot tr th td a img hr figure figcaption sup sub".split(
    " ",
  ),
);
export function unsupportedEditorTags(policy) {
  return policy.tags.filter((tag) => !supportedTags.has(tag));
}
export function editorExtensions(policy) {
  const has = (tag) => policy.tags.includes(tag);
  const levels = [2, 3, 4, 5, 6].filter((level) => has(`h${level}`));
  const extensions = [
    StarterKit.configure({
      bold: has("strong") || has("b") ? {} : false,
      italic: has("em") || has("i") ? {} : false,
      underline: has("u") ? {} : false,
      strike: has("s") ? {} : false,
      code: has("code") ? {} : false,
      codeBlock: has("pre") && has("code") ? {} : false,
      blockquote: has("blockquote") ? {} : false,
      bulletList: has("ul") && has("li") ? {} : false,
      orderedList: has("ol") && has("li") ? {} : false,
      horizontalRule: has("hr") ? {} : false,
      hardBreak: has("br") ? {} : false,
      trailingNode: false,
      heading: levels.length ? { levels } : false,
      link: has("a")
        ? {
            openOnClick: false,
            autolink: false,
            linkOnPaste: true,
            shouldAutoLink: (url) => /^https?:\/\//i.test(url),
            HTMLAttributes: { target: null, rel: null },
          }
        : false,
    }),
  ];
  if (has("a")) extensions.push(Extension.create({
    name: "httpAutoLinks",
    addProseMirrorPlugins() {
      return [new Plugin({
        appendTransaction(transactions, oldState, state) {
          if (!transactions.some(transaction => transaction.docChanged) ||
              transactions.some(transaction => transaction.getMeta("preventAutolink"))) return null;
          const tr = state.tr;
          state.doc.descendants((node, position) => {
            if (!node.isTextblock || node.type.spec.code) return;
            const text = node.textBetween(0, node.content.size, "", "\ufffc");
            for (const match of text.matchAll(/https?:\/\/[^\s<>"\ufffc]+/gi)) {
              const href = match[0].replace(/[.,;:!?]+$/, "");
              if (!/^https?:\/\/[^/]+/i.test(href)) continue;
              const from = position + 1 + match.index;
              const to = from + href.length;
              let code = false;
              state.doc.nodesBetween(from, to, child => { if (child.marks.some(mark => mark.type.name === "code")) code = true; });
              if (!code) tr.addMark(from, to, state.schema.marks.link.create({ href }));
            }
            return false;
          });
          return tr.docChanged ? tr : null;
        },
      })];
    },
  }));
  if (has("table") && has("tr") && has("td"))
    extensions.push(
      TableKit.configure({
        table: { resizable: false },
        tableHeader: has("th") ? {} : false,
      }),
    );
  if (has("img"))
    extensions.push(
      Image.extend({
        addAttributes() {
          return { ...this.parent?.(), "data-media-id": { default: null } };
        },
      }),
    );
  if (has("sub")) extensions.push(Subscript);
  if (has("sup")) extensions.push(Superscript);
  if (has("figure") && has("figcaption") && has("img"))
    extensions.push(
      Node.create({
        name: "figure",
        group: "block",
        content: "(image | figcaption | paragraph)*",
        parseHTML: () => [{ tag: "figure" }],
        renderHTML: () => ["figure", 0],
      }),
      Node.create({
        name: "figcaption",
        content: "inline*",
        parseHTML: () => [{ tag: "figcaption" }],
        renderHTML: () => ["figcaption", 0],
      }),
    );
  extensions.push(
    Extension.create({
      name: "articleAttributes",
      addGlobalAttributes() {
        return [
          {
            types: has("ol") ? ["orderedList"] : [],
            attributes: {
              type: {
                default: null,
                parseHTML: (element) => element.getAttribute("type"),
                renderHTML: (attributes) =>
                  attributes.type ? { type: attributes.type } : {},
              },
            },
          },
          {
            types: has("a") ? ["link"] : [],
            attributes: { title: { default: null } },
          },
        ];
      },
    }),
  );
  return extensions;
}

