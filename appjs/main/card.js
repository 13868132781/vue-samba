var template = `
<!-- 外面添加div，并设置overflow: hidden是避免垂直div margin重叠问题 -->
<div style="overflow: hidden;">
	<div style="background-color:#fff; border-radius:3px; box-shadow:0 0 2px 1px #ccc; margin:5px;padding:10px">
		<slot />
	</div>
</div>
`;
export default{
	template : template,
	
}