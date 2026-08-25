<script setup lang="ts">
import {
  NButton,
  NAvatar,
  NForm,
  NFormItem,
  NInput,
  NUpload,
  NTooltip,
  NDynamicTags,
  useMessage,
  type FormInst,
  type UploadInst,
  type UploadFileInfo,
} from "naive-ui"
import {onMounted, ref, watch} from "vue";
import {postUserProfile} from "@/api";
import {useUserStore} from "@/stores/user";
import app from "@/utils/app";
import type {FormRules} from "naive-ui/es/form/src/interface";
import {FontAwesomeIcon} from "@fortawesome/vue-fontawesome";
import {useI18n} from "vue-i18n";

const message = useMessage()
const userStore = useUserStore()
const { t } = useI18n()

const loading = ref(false)
const formData = ref<any>({})
const formRef = ref<FormInst | null>(null)
const uploadRef = ref<UploadInst | null>(null)
const formRules : FormRules = {
  username: {
    type: 'string',
    required: true,
    trigger: ['blur', 'input'],
    message: t('Please enter your username')
  },
  name: {
    type: 'string',
    required: true,
    trigger: ['blur', 'input'],
    message: t('Please enter a nickname')
  },
}

const onSubmit = async (e: Event) => {
  e.preventDefault()
  formRef.value?.validate(async (errors: any) => {
    if (!errors) {
      loading.value = true

      const response = await postUserProfile({body: formData.value}).finally(() => loading.value = false)

      if (response.data?.status === 'error') {
        return message.error(response.data?.message)
      }

      uploadRef.value?.clear()
      await userStore.fetchUserProfile()

      loading.value = false

      return message.success(t('Successfully updated'))
    }
  })
}

const handleAvatarChange = (options: { fileList: UploadFileInfo[] }) => {
  let file = undefined

  if (options.fileList.length > 0) {
    file = options.fileList[0].file
  }

  if (formData.value) {
    if (file) {
      formData.value.avatar = file
    } else {
      delete formData.value.avatar
    }
  }
}

const setInterests = (value: string[]) => {
  for (const i in value) {
    formData.value[`interests[${i}]`] = value[i]
  }
}

const setSocials = (value: string[]) => {
  for (const i in value) {
    formData.value[`socials[${i}]`] = value[i]
  }
}

const interests = ref(userStore.profile?.interests || [])
const socials = ref(userStore.profile?.socials || [])

watch(interests, value => setInterests(value), {deep: true})
watch(socials, value => setSocials(value), {deep: true})

const addSocialItem = () => {
  socials.value.push('')
}

const removeSocialItem = (index: number) => {
  socials.value.splice(index, 1)
}

onMounted(() => {
  formData.value = {
    name: userStore.profile?.name,
    username: userStore.profile?.username,
    tagline: userStore.profile?.tagline,
    bio: userStore.profile?.bio || '',
    url: userStore.profile?.url || '',
    company: userStore.profile?.company || '',
    company_title: userStore.profile?.company_title || '',
    location: userStore.profile?.location,
  }

  setInterests(interests.value)
  setSocials(socials.value)
})
</script>

<template>
  <NForm
    ref="formRef"
    label-placement="left"
    :label-width="100"
    label-align="left"
    :model="formData"
    :rules="formRules"
    @submit.prevent="onSubmit"
  >
    <NFormItem :label="$t('Avatar')" path="avatar">
      <NUpload
        ref="uploadRef"
        list-type="image"
        :default-upload="false"
        accept=".png,.jpg,.jpeg,.gif"
        :max="1"
        name="avatar"
        @change="handleAvatarChange"
        with-credentials
      >
        <NTooltip trigger="hover" placement="right" :disabled="Boolean(formData?.avatar)">
          {{ $t('Click to upload a new avatar. It will take effect after saving.') }}
          <template #trigger>
            <div class="relative">
              <NAvatar
                round
                :src="app.getUserAvatar(userStore.profile?.avatar_url)"
                class="w-20 h-20 cursor-pointer"
              />
              <div class="absolute bottom-1 right-0 rounded-full bg-white w-7 h-7 flex justify-center items-center border">
                <FontAwesomeIcon icon="fa-pen" />
              </div>
            </div>
          </template>
        </NTooltip>
      </NUpload>
    </NFormItem>

    <NFormItem :label="$t('Username')" path="username">
      <NInput
        v-model:value="formData.username"
        :maxlength="20"
        :placeholder="$t('Please enter your username')"
      />
    </NFormItem>

    <NFormItem :label="$t('Nickname')" path="name">
      <NInput
        v-model:value="formData.name"
        :maxlength="20"
        :placeholder="$t('Please enter a nickname within 20 characters')"
      />
    </NFormItem>

    <NFormItem :label="$t('Personal Signature')" path="tagline">
      <NInput
        v-model:value="formData.tagline"
        :maxlength="100"
        :placeholder="$t('This person is diligent and has written a personal signature')"
      />
    </NFormItem>

    <NFormItem :label="$t('Personal Introduction')" path="bio">
      <NInput
        type="textarea"
        v-model:value="formData.bio"
        :maxlength="200"
        :placeholder="$t('Introduce yourself within 200 characters~')"
      />
    </NFormItem>

    <NFormItem :label="$t('Personal Website')" path="url">
      <NInput
        :input-props="{type: 'url'}"
        v-model:value="formData.url"
        :maxlength="200"
        :placeholder="$t('Enter personal website URL')"
      />
    </NFormItem>

    <NFormItem :label="$t('Company')" path="company">
      <NInput
        v-model:value="formData.company"
        :maxlength="80"
        :placeholder="$t('Enter company name')"
      />
    </NFormItem>

    <NFormItem :label="$t('Position at Company')" path="company_title">
      <NInput
        v-model:value="formData.company_title"
        :maxlength="20"
        :placeholder="$t('Enter your position at the company')"
      />
    </NFormItem>

    <NFormItem :label="$t('Location')" path="location">
      <NInput
        v-model:value="formData.location"
        :maxlength="80"
        :placeholder="$t('Enter location')"
      />
    </NFormItem>

    <NFormItem :label="$t('Hobbies')">
      <NDynamicTags
        v-model:value="interests"
        :max="9"
      />
    </NFormItem>

    <NFormItem :label="index === 0 ? $t('Social Accounts') : '&nbsp;'"
      v-if="socials.length > 0"
      v-for="(_, index) in socials"
      :path="`socials[${index}]`"
      :rule="{
        required: true,
        message: $t('Please enter the link address'),
        trigger: ['input', 'blur'],
      }"
    >
      <NInput
        :input-props="{type: 'url', required: true}"
        v-model:value="socials[index]"
        :maxlength="60"
        :placeholder="$t('Link to social profile')"
        clearable
      >
        <template #prefix>
          <FontAwesomeIcon icon="fa-link" class="text-gray-500" />
        </template>
      </NInput>

      <NButton class="ml-2" @click="removeSocialItem(index)">{{ $t('Delete') }}</NButton>
    </NFormItem>

    <NFormItem label="&nbsp;">
      <NButton @click="addSocialItem()" v-if="socials.length < 6">{{ $t('Add') }}</NButton>
    </NFormItem>

    <NFormItem :show-feedback="false">
      <NButton
        attr-type="submit"
        type="primary"
        :loading="loading"
      >{{ $t('Confirm Save') }}</NButton>
    </NFormItem>
  </NForm>
</template>