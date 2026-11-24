            <dialog id="rfd">
                <form id="rff" class="rff">
                    <h3>Refund</h3>
                    <p id="rfinfo" class="mu"></p>
                    <label>Status<select name="st">
                            <option value="pending">Refund pending</option>
                            <option value="refunded">Refunded</option>
                            <option value="none">No refund (hotel keeps it)</option>
                        </select></label>
                    <label>Amount (₱)<input type="number" name="amount" min="0" step="0.01"></label><label>Note (e.g. GCash
                        reference)<input name="note" maxlength="120"></label>
                    <p id="rferr" class="err"></p>
                    <div class="row"><button class="btn">Save</button><button type="button" class="btn ghost"
                            id="rfx">Close</button></div>
                </form>
            </dialog>
