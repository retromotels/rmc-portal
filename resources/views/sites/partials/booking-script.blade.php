{{-- Shared booking date logic. Themes provide #ci #co #ad #bookBtn (btn carries data-book). --}}
<script>
(function(){
  var ci=document.getElementById('ci'), co=document.getElementById('co'),
      ad=document.getElementById('ad'), btn=document.getElementById('bookBtn');
  if(!btn) return;
  var iso=function(d){return d.toISOString().slice(0,10);};
  if(ci&&co){
    var t=new Date(), tm=new Date(Date.now()+864e5);
    ci.value=iso(t); ci.min=iso(t); co.value=iso(tm); co.min=iso(tm);
    ci.addEventListener('change',function(){
      if(co.value<=ci.value){ co.value=iso(new Date(new Date(ci.value).getTime()+864e5)); }
      co.min=ci.value;
    });
  }
  btn.addEventListener('click',function(){
    var base=btn.dataset.book||'#', url=base;
    try{
      var u=new URL(base, location.href);
      if(ci) u.searchParams.set('checkin', ci.value);
      if(co) u.searchParams.set('checkout', co.value);
      if(ad) u.searchParams.set('adults', ad.value);
      url=u.toString();
    }catch(e){}
    window.open(url,'_blank','noopener');
  });
})();
</script>
