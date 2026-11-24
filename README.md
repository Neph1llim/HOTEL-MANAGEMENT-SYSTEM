# Bella Vista - setup and demo

**Fresh install:** phpMyAdmin > Import `database.sql`, then `demo-data.sql`.
**Already had an older copy:** import `update3.sql` once (keeps your data), then `demo-data.sql` if you want sample data.
**Restart the demo any time:** select the `bellavista` database > Import > `reset-demo.sql` (wipes everything, reloads the sample data with fresh dates).
**Empty system after the defense:** import `clear-all-data.sql`.

Logins: admin / admin123 | maria@demo.com, juan@demo.com, carlo@demo.com, ana@demo.com, liza@demo.com / guest123

## Business rules (for the defense)
- Unpaid hold: an online reservation with no payment proof is cancelled after 24 hours and the room is released (the voucher is returned). A rejected proof restarts the 24 hours. Proofs awaiting review never expire.
- Auto-complete: an approved reservation becomes Completed after check-out time (12:00 NN); 1% of the total is added as points. No admin action needed.
- Refunds: cancelling 3+ days before check-in marks verified payments "refund pending"; the admin records the refund (amount, note) as Refunded. Less than 3 days = downpayment kept.
- Balance: Total minus verified payments. Admin presses "Collect balance" at check-in (recorded as a cash payment).
- Walk-ins: Admin > Walk-in booking; approved immediately, downpayment recorded as cash.
- Rooms: Admin > Rooms & rates (rate, capacity, on/off). New rates apply to future bookings only.
- Rules run automatically whenever any page is opened (no cron job needed). Times use Asia/Manila.
- Overview tab: shows one calendar month at a time (arrows in the header, up to 1 month ahead). Reservations count in the month they were booked, payments in the month they were submitted. It only lists items that still need action; everything stays on the Payments and Reservations tabs. A new month starts empty by itself, nothing is deleted.
- Payments: a verified or rejected payment can be set back to pending (Payments tab > Revert to pending). Not possible once the reservation is completed or refunded.
- Reservations: Complete appears only after the balance is collected; Refund appears only while a refund is still owed.
- Rooms & Rates: edit any rows and press Save All Changes once. Delete Room removes a room; a room with booking history is hidden instead of erased (reports keep its name), and a room with pending or upcoming bookings cannot be deleted until those are cancelled or completed. (update4.sql adds the needed column; the system also adds it automatically.)
- Payments and Reservations pages have a search box (name, email, contact no., reference no.) that searches all records.
