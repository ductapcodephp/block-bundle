(function () {
    'use strict';

    var container = document.getElementById('block-preview-shadow-container');
    if (!container) return;

    var updateUrl = container.getAttribute('data-update-url');
    var addItemUrl = container.getAttribute('data-add-item-url');
    var deleteItemUrl = container.getAttribute('data-delete-item-url');
    var updateListingFieldUrl = container.getAttribute('data-update-listing-field-url') ||
        (deleteItemUrl ? deleteItemUrl.replace('/delete/listing-item/', '/update/listing-field/') : '');

    var shadowRoot = container.attachShadow({ mode: 'open' });
    shadowRoot.innerHTML = container.innerHTML;
    container.innerHTML = '';

    var activeGalleryProp = null;
    var activeListingItemUuid = null;


    function toastSuccess(title) {
        Swal.fire({
            toast: true, position: 'top-end', icon: 'success',
            title: title, showConfirmButton: false,
            timer: 3000, timerProgressBar: true, buttonsStyling: false,
        });
    }

    function alertError(title, html) {
        Swal.fire({
            icon: 'error', title: title, html: html,
            timer: 2500, timerProgressBar: true, buttonsStyling: false,
            confirmButtonText: 'Ok, hiểu rồi!',
            customClass: { confirmButton: 'btn btn-danger' },
        });
    }

    function showLoading(title) {
        Swal.fire({ title: title || 'Đang xử lý...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
    }


    function postForm(url, fields) {
        var formData = new FormData();
        Object.entries(fields).forEach(([k, v]) => formData.append(k, v));
        return fetch(url, {
            method: 'POST', body: formData,
            headers: { Accept: 'application/json' },
            credentials: 'same-origin',
        }).then(res => {
            if (!res.ok) throw new Error('HTTP ' + res.status + ' ' + res.statusText);
            return res.json();
        });
    }

    function sendInstantUpdate(propName, value) {
        postForm(updateUrl, { [propName]: value })
            .then(data => {
                if (data.status === 'success') {
                    toastSuccess('Đã lưu: ' + propName);
                } else {
                    alertError('Lưu thất bại!', data.message || 'Máy chủ từ chối lưu dữ liệu.');
                }
            })
            .catch(err => alertError('Lỗi kết nối!',
                'Không thể lưu <b>' + propName + '</b>.<br><small class="text-muted">' + err.message + '</small>'));
    }


    var clickHandlers = {
        '[data-action="open_ckeditor"]': function (target) {
            var descDiv = target.closest('[data-action="open_ckeditor"]');
            if (!window.KTCKEditor4?.openPopupEditor) {
                return Swal.fire({
                    icon: 'warning', title: 'Thiếu thư viện', text: 'Thư viện KTCKEditor4 chưa được tải.',
                    timer: 2500, timerProgressBar: true, buttonsStyling: false,
                    confirmButtonText: 'Ok, hiểu rồi!', customClass: { confirmButton: 'btn btn-warning' },
                });
            }
            window.KTCKEditor4.openPopupEditor(descDiv, updatedHtml => {
                var listingProp = descDiv.getAttribute('data-prop-listing-field');
                if (listingProp) {
                    descDiv.innerHTML = updatedHtml; // Chỉ đổi DOM tạm thời
                } else {
                    sendInstantUpdate(descDiv.getAttribute('data-prop') || 'description', updatedHtml);
                }
            });
        },

        '[data-amzs-gallery-modal]': function (target) {
            activeGalleryProp = target.closest('[data-amzs-gallery-modal]').getAttribute('data-prop');
            document.dispatchEvent(new CustomEvent('amzsGalleryOpen'));
        },

        '[data-action="open_gallery"]': function (target) {
            var itemDiv = target.closest('[data-item-uuid]');
            if (itemDiv) {
                activeListingItemUuid = itemDiv.getAttribute('data-item-uuid');
                document.dispatchEvent(new CustomEvent('amzsGalleryOpen'));
            }
        },

        '[data-action="add_item"]': function () {
            if (!addItemUrl) return Swal.fire('Lỗi', 'Không tìm thấy cấu hình URL thêm item.', 'error');
            showLoading('Đang tạo mới...');
            fetch(addItemUrl, { method: 'POST', headers: { Accept: 'application/json' } })
                .then(res => res.json())
                .then(data => data.status === 'success' ? window.location.reload()
                    : Swal.fire('Lỗi', data.message || 'Lỗi thêm dữ liệu', 'error'))
                .catch(err => Swal.fire('Lỗi', err.message, 'error'));
        },

        '[data-action="delete_item"]': function (target) {
            var itemDiv = target.closest('[data-item-uuid]');
            var uuid = itemDiv?.getAttribute('data-item-uuid');

            if (!deleteItemUrl || !uuid)
                return Swal.fire('Lỗi', 'Không tìm thấy ID của item cần xóa.', 'error');

            Swal.fire({
                title: 'Xóa item này?', text: 'Hành động này không thể khôi phục!',
                icon: 'warning', showCancelButton: true,
                confirmButtonText: 'Xóa ngay', cancelButtonText: 'Hủy',
            }).then(result => {
                if (!result.isConfirmed) return;
                postForm(deleteItemUrl, { uuid: uuid, id: uuid })
                    .then(data => {
                        if (data.status === 'success') {
                            if (itemDiv) itemDiv.remove();
                            toastSuccess('Đã xóa thành công');
                        } else {
                            Swal.fire('Lỗi', data.message || 'Lỗi khi xóa', 'error');
                        }
                    })
                    .catch(err => alertError('Lỗi kết nối', err.message));
            });
        },

        '[data-action="save_item"]': function (target) {
            var itemDiv = target.closest('[data-item-uuid]');
            var uuid = itemDiv?.getAttribute('data-item-uuid');

            if (!itemDiv || !uuid)
                return Swal.fire({ icon: 'error', title: 'Lỗi cấu trúc', text: 'Không tìm thấy khối hoặc ID tương ứng với item.' });

            if (!updateListingFieldUrl)
                return Swal.fire({ icon: 'error', title: 'Lỗi cấu hình', text: 'Không xác định được đường dẫn lưu Listing Field.' });

            showLoading('Đang lưu thay đổi...');

            var payload = {};

            var titleInput = itemDiv.querySelector('input[data-prop-listing-field="title"]');
            if (titleInput) payload['title'] = titleInput.value.trim();

            var descDiv = itemDiv.querySelector('[data-prop-listing-field="description"]');
            if (descDiv) payload['description'] = descDiv.innerHTML.trim();

            var imgEl = itemDiv.querySelector('img[data-prop-listing-field="image"]');
            if (imgEl) payload['image'] = imgEl.getAttribute('data-pending-value') || '';

            var savePromises = Object.entries(payload).map(([field, value]) => {
                return postForm(updateListingFieldUrl, {
                    uuid: uuid,
                    field: field,
                    value: value,
                    key_items: 'listingItem'
                });
            });

            Promise.all(savePromises)
                .then(results => {
                    var isAllOk = results.every(res => res.status === 'success');
                    if (isAllOk) {
                        toastSuccess('Dữ liệu của item đã được lưu trữ thành công!');
                    } else {
                        alertError('Lưu dữ liệu!', 'Một số thông tin lưu trữ gặp lỗi.');
                    }
                })
                .catch(err => alertError('Lỗi hệ thống', 'Không thể hoàn tất tiến trình lưu trữ: ' + err.message));
        },
    };

    shadowRoot.addEventListener('click', function (e) {
        for (var selector in clickHandlers) {
            if (e.target.closest(selector)) {
                e.preventDefault();
                e.stopPropagation();
                clickHandlers[selector](e.target);
                return;
            }
        }
    });


    shadowRoot.addEventListener('focusout', function (e) {
        var el = e.target;
        if (el.getAttribute?.('contenteditable') !== 'true') return;
        if (el.getAttribute('data-action') === 'open_ckeditor') return;

        var propName = el.getAttribute('data-prop');
        if (propName) sendInstantUpdate(propName, el.innerText.trim());
    });


    document.addEventListener('amzsGalleryPicked', function (e) {
        var picture = e.detail.pictures?.[0];
        if (!picture) {
            Swal.fire({ icon: 'warning', title: 'Chọn ảnh', text: 'Chưa chọn ảnh nào.',
                buttonsStyling: false, confirmButtonText: 'Ok, hiểu rồi!',
                customClass: { confirmButton: 'btn btn-warning' } });
            activeGalleryProp = null;
            activeListingItemUuid = null;
            return;
        }

        var path;
        try { path = new URL(picture.image).pathname; }
        catch { path = picture.image; }

        if (activeListingItemUuid) {
            var itemContainer = shadowRoot.querySelector('[data-item-uuid="' + activeListingItemUuid + '"]');
            if (itemContainer) {
                var targetImg = itemContainer.querySelector('img[data-prop-listing-field="image"]');
                if (targetImg) {
                    targetImg.src = path;
                    targetImg.setAttribute('data-pending-value', path);
                }
            }
            activeListingItemUuid = null;
            return;
        }

        if (activeGalleryProp) {
            var img = shadowRoot.querySelector('[data-preview-target="' + activeGalleryProp + '"]');
            if (img) img.src = path;

            sendInstantUpdate(activeGalleryProp, path);
            activeGalleryProp = null;
        }
    });


    var searchInput = shadowRoot.getElementById('block-search-input');
    if (searchInput) {
        searchInput.addEventListener('input', function (e) {
            var searchTerm = e.target.value.toLowerCase().trim();
            var items = shadowRoot.querySelectorAll('[data-item-uuid]');

            items.forEach(function (item) {
                var itemVisible = false;
                var fieldsToSearch = item.querySelectorAll('[data-prop-listing-field]');
                fieldsToSearch.forEach(function (field) {
                    var fieldValue = field.innerText || field.value || field.getAttribute('data-pending-value') || '';
                    if (fieldValue.toLowerCase().includes(searchTerm)) {
                        itemVisible = true;
                    }
                });

                if (itemVisible) {
                    item.style.display = '';
                } else {
                    item.style.display = 'none';
                }
            });
        });
    }
})();