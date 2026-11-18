import 'jquery'
declare global {
  interface JQuery<TElement = HTMLElement> {
    markItUp(options: object | 'remove'): JQuery<TElement>
  }
}
export default function installMarkItUp(jquery: JQueryStatic): void
