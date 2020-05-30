import { el } from '../core/helpers'
//import uiux from '../core/uiux'

export default {
  init() {
    this.build();
  },
  build() {
    $(el("field", true, "--multiple :input + label")).click(function(){
      $(this).prev().focus();
    });
  },
}