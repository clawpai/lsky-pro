import {defineStore} from "pinia";
import {deleteExplorePhotosByIdUnlike, postExploreAlbumsByIdLike, postExplorePhotosByIdLike} from "@/api";

export const useExploreStore = defineStore('explore', () => {

  /**
   * 点赞图片
   */
  async function photoLiked(id: number) {
    return await postExplorePhotosByIdLike({
      path: {
        id: id,
      }
    })
  }

  /**
   * 取消点赞图片
   */
  async function photoUnlinked(id: number) {
    return await deleteExplorePhotosByIdUnlike({
      path: {
        id: id,
      }
    })
  }

  return {
    photoLiked,
    photoUnlinked,
  }
})