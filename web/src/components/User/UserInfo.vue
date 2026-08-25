<script setup lang="ts">
import { NAvatar, NIcon, NDropdown, NButton, NTag } from "naive-ui"
import {
  LinkOutline as LinkOutlineIcon,
  LocationOutline as LocationOutlineIcon,
} from "@vicons/ionicons5";
import {computed, ref} from "vue";
import type {DropdownMixedOption} from "naive-ui/es/dropdown/src/interface";
import Report from "@/components/Explore/Report.vue";
import app from "@/utils/app";

const reportRef = ref<any>(null)

const props = defineProps({
  user: {
    type: Object,
    required: true,
    default: () => {}
  }
})

const socials = computed<DropdownMixedOption[]>(() => {
  return (props.user?.socials || []).map((url: string) => ({
    label: url,
    props: {
      onClick: () => window.open(url)
    }
  }))
})

function report() {
  reportRef.value?.open({
    path: {
      username: props.user.username
    }
  })
}

</script>

<template>
  <Report ref="reportRef" type="user" />

  <div class="flex flex-col sm:flex-row sm:space-x-16 w-full p-6">
    <div class="flex basis-1/3 justify-center sm:justify-end shrink-0">
      <NAvatar
        round
        :size="120"
        :src="app.getUserAvatar(props.user.avatar_url)"
      />
    </div>

    <div class="flex grow justify-start flex-col space-y-4">
      <div class="flex items-center mb-2">
        <div class="text-3xl font-bold sm:mr-2">{{ props.user.name }}</div>
        <NButton secondary strong size="tiny" @click="report">{{ $t('Report') }}</NButton>
      </div>
      <p>{{ props.user.bio }}</p>
      <p class="flex items-center space-x-1" v-if="props.user.location">
        <NIcon :size="16" :component="LocationOutlineIcon" /> <span>{{ props.user.location }}</span>
      </p>
      <div v-if="socials">
        <NDropdown
          placement="bottom-start"
          trigger="click"
          :options="socials"
          show-arrow
        >
          <NButton text>
            <template #icon>
              <NIcon :component="LinkOutlineIcon" />
            </template>
            {{ $t('Social Accounts') }}
          </NButton>
        </NDropdown>
      </div>
      <div v-if="props.user?.interests">
        <p class="text-base mb-4">{{ $t('Interested Tags') }}</p>
        <div class="flex flex-wrap gap-3">
          <NTag :bordered="false" v-for="interest in props.user?.interests || []">
            {{ interest }}
          </NTag>
        </div>
      </div>
    </div>
  </div>
</template>