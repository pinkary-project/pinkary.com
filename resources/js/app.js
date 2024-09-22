import './bootstrap'
import autosize from 'autosize';
import notifications from 'alpinejs-notify'
import Sortable from 'sortablejs'
import { Livewire, Alpine } from '../../vendor/livewire/livewire/dist/livewire.esm'

window.Alpine = Alpine
window.Sortable = Sortable

Alpine.plugin(notifications)

Alpine.magic('clipboard', () => {
    return subject => navigator.clipboard.writeText(subject)
})

Alpine.directive('sortable', (el) => {
    el.sortable = Sortable.create(el, {
        draggable: '[x-sortable-item]',
        handle: '[x-sortable-handle]',
        dataIdAttr: 'x-sortable-item',
        animation: 300,
        ghostClass: 'opacity-30',
    })
})

Alpine.directive('autosize', (el) => {
    if (el) {
        autosize(el);
    }
});

import { shareProfile } from './share-profile.js'
Alpine.data('shareProfile', shareProfile)

import { copyUrl } from './copy-url.js'
Alpine.data('copyUrl', copyUrl)

import { showMore } from './show-more.js'
Alpine.data('showMore', showMore)

import { clickHandler } from './click-handler.js'
Alpine.data('clickHandler', clickHandler)

import { copyCode } from './copy-code.js'
Alpine.data('copyCode', copyCode)

import { imageUpload } from './image-upload.js'
Alpine.data('imageUpload', imageUpload)

import { autocomplete, usesAutocomplete } from "./autocomplete.js";
Alpine.data('dynamicAutocomplete', autocomplete);
Alpine.data('usesDynamicAutocomplete', usesAutocomplete);

import { hasLightBoxImages, lightBox } from './light-box.js';
Alpine.data('hasLightBoxImages', hasLightBoxImages);
Alpine.data('lightBox', lightBox);

import { likeButton } from './like-button.js';
Alpine.data('likeButton', likeButton);

import { bookmarkButton } from './bookmark-button.js';
Alpine.data('bookmarkButton', bookmarkButton);
import { followButton } from './follow-button.js'
Alpine.data('followButton', followButton)

import { viewCreate } from './view-cerate.js';
Alpine.data('viewCreate', viewCreate);

import { themeSwitch } from './theme-switch.js';
Alpine.data('themeSwitch', themeSwitch);

Livewire.start()
