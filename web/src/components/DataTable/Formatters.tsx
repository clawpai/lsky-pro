import {h} from 'vue';
import {type InputProps, NInput} from "naive-ui";

function password(text: string, props: InputProps = {}) {
  return h(NInput, {
    ...{
      type: 'password',
      showPasswordOn: 'click',
      defaultValue: text,
    }, ...props
  });
}

export default {
  password
}