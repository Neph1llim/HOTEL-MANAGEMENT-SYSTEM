<?php include 'head.php'; ?>
<style>
/* reservation cards (scoped to this page) */
#bklist { grid-template-columns: repeat(auto-fill, minmax(min(100%, 340px), 1fr)); gap: 1.2rem }
.bklink { margin: -.2rem 0 1.2rem }
.bklink a { color: var(--acc-tx, #86640F); font-weight: 600; text-decoration: none }
.bklink a:hover { text-decoration: underline }
.bk { position: relative; display: flex; flex-direction: column; gap: .9rem; overflow: hidden; padding: 1.4rem 1.3rem 1.2rem }
.bk::before { content: ""; position: absolute; inset: 0 0 auto; height: 3px; background: linear-gradient(90deg, #B88E28, #E8C868, #B88E28) }
.bkh { display: flex; align-items: flex-start; justify-content: space-between; gap: .8rem }
.bkh h3 { margin: 0; font-size: 1.55rem; line-height: 1.1 }
.bkref { flex: none; padding: .25rem .65rem; border: 1px dashed #B88E28; border-radius: 8px; font: 600 .74rem/1.2 var(--nav, Manrope), system-ui, sans-serif; letter-spacing: .08em; color: var(--acc-tx, #86640F) }
.bkpills { display: flex; flex-wrap: wrap; gap: .4rem }
.bkp { display: inline-block; padding: .22rem .75rem; border-radius: 999px; font: 600 .72rem/1.3 var(--nav, Manrope), system-ui, sans-serif; letter-spacing: .05em; text-transform: capitalize; border: 1px solid transparent }
.bkp.pending { background: rgba(212, 175, 55, .18); border-color: rgba(212, 175, 55, .55); color: #86640F }
.bkp.approved { background: rgba(43, 110, 71, .14); border-color: rgba(43, 110, 71, .4); color: #2B6E47 }
.bkp.completed { background: #113320; border-color: #113320; color: #E8C868 }
.bkp.cancelled { background: rgba(166, 75, 60, .12); border-color: rgba(166, 75, 60, .4); color: #A64B3C }
[data-theme=dark] .bkp.pending { color: #E8C868 } [data-theme=dark] .bkp.approved { color: #7FD1A0 }
[data-theme=dark] .bkp.completed { background: #1B4D32; border-color: #1B4D32 } [data-theme=dark] .bkp.cancelled { color: #E08A78 }
.bkmeta { display: grid; grid-template-columns: repeat(auto-fit, minmax(105px, 1fr)); gap: .7rem 1rem; margin: 0; padding: .9rem 0; border-block: 1px solid var(--ln) }
.bkmeta dt { font-size: .7rem; letter-spacing: .08em; text-transform: uppercase; color: var(--mu) }
.bkmeta dd { margin: .1rem 0 0; font-weight: 600 }
.bkstate:empty { display: none }
.bkstate p { margin: 0 0 .35rem; font-size: .92rem }
.bkact { display: flex; flex-wrap: wrap; gap: .6rem; margin-top: auto }
.bkact .btn { flex: 1 1 140px; justify-content: center; text-align: center }
</style>
<section class="wrap">
    <h1 data-t>My reservations</h1>
    <p class="bklink"><a href="notifications.php"><span data-t>Notifications</span> &rarr;</a></p>
    <div id="bklist" class="grid"></div>
</section>
<?php include 'foot.php'; ?>
