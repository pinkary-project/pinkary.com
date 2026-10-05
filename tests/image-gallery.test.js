import assert from 'node:assert/strict';
import test from 'node:test';
import { galleryHeight, galleryScrollTarget } from '../resources/js/image-gallery.js';
import { lightBox } from '../resources/js/light-box.js';

test('keeps a shared height that fits the widest image without cropping', () => {
    assert.equal(galleryHeight(320, [{ width: 800, height: 400 }, { width: 400, height: 800 }]), 160);
});

test('caps desktop gallery height and ignores images that have not loaded', () => {
    assert.equal(galleryHeight(1000, [{ width: 800, height: 400 }, { width: 0, height: 0 }]), 256);
    assert.equal(galleryHeight(320, []), 256);
});

test('moves forwards and backwards between natural-width images', () => {
    assert.equal(galleryScrollTarget([0, 320, 450], 0, 580, 1), 320);
    assert.equal(galleryScrollTarget([0, 320, 450], 320, 580, 1), 450);
    assert.equal(galleryScrollTarget([0, 320, 450], 450, 580, -1), 320);
});

test('clamps arrow navigation at either end and handles a strip that fits', () => {
    assert.equal(galleryScrollTarget([0, 400, 800], 400, 520, 1), 520);
    assert.equal(galleryScrollTarget([0, 400, 800], 520, 520, 1), 520);
    assert.equal(galleryScrollTarget([0, 400, 800], 0, 520, -1), 0);
    assert.equal(galleryScrollTarget([0], 0, 0, 1), 0);
});

test('does not navigate an empty or single-image lightbox', () => {
    const viewer = lightBox();

    viewer.nextImage();
    viewer.prevImage();
    assert.equal(viewer.currentIndex, 0);
    assert.equal(viewer.imgSrc, '');

    viewer.images = [{ src: '/one.png' }];
    viewer.nextImage();
    assert.equal(viewer.currentIndex, 0);
});
