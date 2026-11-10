// This file is part of Moodle - https://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

import Ajax from 'core/ajax';
import Notification from 'core/notification';
import ModalSaveCancel from 'core/modal_save_cancel';
import ModalEvents from 'core/modal_events';
import {add as addToast} from 'core/toast';
import {get_strings as getStrings} from 'core/str';

const COMPONENT = 'local_chuspasocial';

/**
 * Calls the external function that reports an item.
 *
 * @param {Number} itemid Id of the reported item.
 * @param {String} reason Reason written by the user.
 * @returns {Promise} Promise resolved with the response of the service.
 */
const sendReport = (itemid, reason) => Ajax.call([{
    methodname: 'local_chuspasocial_report_item',
    args: {
        itemid,
        reason,
    },
}])[0];

/**
 * Opens the modal that asks for the reason and sends the report.
 *
 * @param {Number} itemid Id of the item to report.
 * @returns {Promise} Promise resolved when the modal is open.
 */
const openModal = async(itemid) => {
    const [title, label, sent] = await getStrings([
        {key: 'report', component: COMPONENT},
        {key: 'reportreason', component: COMPONENT},
        {key: 'reportsent', component: COMPONENT},
    ]);

    const fieldid = 'chuspasocial-report-reason';
    const body = '<div class="form-group">' +
        '<label for="' + fieldid + '">' + label + '</label>' +
        '<textarea id="' + fieldid + '" class="form-control" rows="4"></textarea>' +
        '</div>';

    const modal = await ModalSaveCancel.create({
        title,
        body,
        show: true,
        removeOnClose: true,
    });
    modal.setSaveButtonText(title);

    modal.getRoot().on(ModalEvents.save, (event) => {
        // Keep the modal open until the report is sent or the reason is valid.
        event.preventDefault();

        const textarea = modal.getRoot()[0].querySelector('#' + fieldid);
        const reason = textarea.value.trim();

        if (reason === '') {
            textarea.classList.add('is-invalid');
            textarea.focus();
            return;
        }

        sendReport(itemid, reason).then((response) => {
            if (response.status) {
                modal.hide();
                addToast(sent);
            }
            return null;
        }).catch((error) => {
            modal.hide();
            Notification.exception(error);
        });
    });
};

/**
 * Initializes the report buttons.
 *
 * @module     local_chuspasocial/report
 * @copyright  2026 SIS-INF
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
export const init = () => {
    document.addEventListener('click', (event) => {
        const button = event.target.closest('[data-action="report"]');

        if (!button) {
            return;
        }

        event.preventDefault();

        const itemid = Number(button.dataset.postid);

        if (!itemid) {
            return;
        }

        openModal(itemid).catch(Notification.exception);
    });
};
