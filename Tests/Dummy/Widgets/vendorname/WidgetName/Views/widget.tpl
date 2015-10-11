{% if sMessage|Exists %}
<script>
    alert({{sMessage}});
</script>
{% else %}
<p>Unable to load script...</p>
{% endif %}