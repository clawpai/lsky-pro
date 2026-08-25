<script setup lang="ts">
import Content from "@/components/Content.vue";
import {
  NCard,
  NTabs,
  NTabPane
} from "naive-ui";
import Info from "@/views/user/profile/Info.vue";
import Password from "@/views/user/profile/Password.vue";
import Social from "@/views/user/profile/Social.vue";
import Phone from "@/views/user/profile/Phone.vue";
import Email from "@/views/user/profile/Email.vue";
import {useConfigStore} from "@/stores/config";
import {ref} from "vue";
import {useRoute, useRouter} from "vue-router";
import Layout from "@/components/Layout.vue";

const route = useRoute()
const router = useRouter()
const configStore = useConfigStore()
const tab = ref(route.query.tab ? route.query.tab.toString() : 'basic')

const onChange = (value: string) => {
  router.replace(`/user/profile?tab=${value}`)
}

</script>

<template>
  <Layout
    :header-title="$t('Profile')"
    :toggle-header="false"
    :show-footer="false"
  >
    <Content class="mx-auto p-4 md:p-10 space-y-10">
      <NTabs
        type="line"
        :default-value="tab"
        :on-update-value="onChange"
        animated
      >
        <NTabPane name="basic" :tab="$t('Personal Information')">
          <NCard>
            <Info/>
          </NCard>
        </NTabPane>
        <NTabPane name="email" :tab="$t('Email')">
          <NCard>
            <Email/>
          </NCard>
        </NTabPane>
        <NTabPane name="phone" :tab="$t('Mobile Number')" v-if="configStore.configs?.app.user_phone_verify">
          <NCard>
            <Phone/>
          </NCard>
        </NTabPane>
        <NTabPane name="social" :tab="$t('Third-Party Accounts')">
          <NCard>
            <Social/>
          </NCard>
        </NTabPane>
        <NTabPane name="password" :tab="$t('Change Password')">
          <NCard>
            <Password/>
          </NCard>
        </NTabPane>
      </NTabs>
    </Content>
  </Layout>
</template>