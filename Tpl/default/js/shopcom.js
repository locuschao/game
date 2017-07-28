
function checkAll(obj) {
    $('.chk').each(function () {
        $(this)[0].checked = obj.checked;
    })
}
function getChks() {
    var ids = [];
    $('.chk').each(function () {
        if ($(this)[0].checked)ids.push($(this).val());
    });
    return ids.join(',');
}
/****************************商家团购活动操作**************************/
//查找团购订单
function queryGroup() {
    var shopCatId1 = $('#shopCatId1').val();
    var shopCatId2 = $('#shopCatId2').val();
    var goodsName = $('#goodsName').val();
    var goodsGroupStatus = $('#goodsGroupStatus').val();
    var groupStatus = $('#groupStatus').val();
    location.href = Think.U('Home/Orders/toShopGroupOrders', 'goodsName=' + goodsName + "&shopCatId1=" + shopCatId1
        + "&shopCatId2=" + shopCatId2 + "&goodsGroupStatus=" + goodsGroupStatus + "&groupStatus=" + groupStatus);
}
//查看团购活动的订单
function queryGroupOrders(id) {
    location.href = Think.U('Home/Orders/toShopGroupOrdersLists', 'id=' + id);
}
//查看团购活动的订单
function querySeckillOrders(skId) {
    location.href = Think.U('Home/Orders/toShopSeckillOrdersLists', 'skId=' + skId);
}
/****************************商品操作**************************/
function queryUnSaleByPage() {
    var shopCatId1 = $('#shopCatId1').val();
    var shopCatId2 = $('#shopCatId2').val();
    var goodsName = $('#goodsName').val();
    location.href = Think.U('Home/Goods/queryUnSaleByPage', 'goodsName=' + goodsName + "&shopCatId1=" + shopCatId1 + "&shopCatId2=" + shopCatId2);
}
function queryOnSale() {
    var shopCatId1 = $('#shopCatId1').val();
    var shopCatId2 = $('#shopCatId2').val();
    var goodsName = $('#goodsName').val();
    location.href = Think.U('Home/Goods/queryOnSaleByPage', 'goodsName=' + goodsName + "&shopCatId1=" + shopCatId1 + "&shopCatId2=" + shopCatId2);
}
function queryPassGoods() {
    var shopCatId1 = $('#shopCatId1').val();
    var shopCatId2 = $('#shopCatId2').val();
    var goodsName = $('#goodsName').val();
    location.href = Think.U('Home/Goods/queryPassGoodsByPage', 'goodsName=' + goodsName + "&shopCatId1=" + shopCatId1 + "&shopCatId2=" + shopCatId2);
}
function querySeckillAction() {
    var shopCatId1 = $('#shopCatId1').val();
    var shopCatId2 = $('#shopCatId2').val();
    var goodsName = $('#goodsName').val();
    var status = $('#status').val();
    location.href = Think.U('Home/Orders/toShopSeckillOrders', 'goodsName=' + goodsName + "&shopCatId1=" + shopCatId1 + "&shopCatId2=" + shopCatId2 + "&status=" + status);
}
function queryAuctionAction() {
    var shopCatId1 = $('#shopCatId1').val();
    var shopCatId2 = $('#shopCatId2').val();
    var goodsName = $('#goodsName').val();
    var goodsAuctionStatus = $('#goodsAuctionStatus').val();
    location.href = Think.U('Home/Orders/toShopAuctionOrders', 'goodsName=' + goodsName + "&shopCatId1=" + shopCatId1 + "&shopCatId2=" + shopCatId2 + "&goodsAuctionStatus=" + goodsAuctionStatus);
}
function queryPendding() {
    var shopCatId1 = $('#shopCatId1').val();
    var shopCatId2 = $('#shopCatId2').val();
    var goodsName = $('#goodsName').val();
    location.href = Think.U('Home/Goods/queryPenddingByPage', 'goodsName=' + goodsName + "&shopCatId1=" + shopCatId1 + "&shopCatId2=" + shopCatId2);
}
function toEditGoods(id, menuId) {
    location.href = Think.U('Home/Goods/toEdit', 'umark=' + menuId + "&id=" + id);
}
function toEditAuctionGoods(id, menuId) {
    location.href = Think.U('Home/Goods/toEditAuctionGoods', 'umark=' + menuId + "&id=" + id);
}
function toViewGoods(id) {
    $.post(Think.U('Home/Goods/getGoodsVerify'), {id: id}, function (data, textStatus) {
        var json = WST.toJson(data);
        if (json.status == '1') {
            var verifyCode = json.verifyCode;
            window.open(Think.U('Home/Goods/getGoodsDetails', 'goodsId=' + id + "&kcode=" + verifyCode));
        }
    });

}
function delGoods(id) {
    layer.confirm("您确定要删除该商品？", {icon: 3, title: '系统提示'}, function (tips) {
        $.post(Think.U('Home/Goods/del'), {id: id}, function (data, textStatus) {
            layer.close(tips);
            var json = WST.toJson(data);
            if (json.status == '1') {
                WST.msg('操作成功！', {icon: 1}, function () {
                    location.reload();
                });
            } else {
                WST.msg('操作失败', {icon: 5});
            }
        });
    });
}
function batchDel() {
    layer.confirm("您确定要删除这些商品？", {icon: 3, title: '系统提示'}, function () {
        var ids = getChks();
        var loading = layer.load('正在处理，请稍后...', 3);
        var params = {};
        params.ids = ids;
        $.post(Think.U('Home/Goods/batchDel'), params, function (data, textStatus) {
            var json = WST.toJson(data);
            if (json.status == '1') {
                WST.msg('操作成功！', {icon: 1}, function () {
                    $('.chk').prop('checked',false);
                    location.reload();
                });
            } else {
                layer.close(loading);
                WST.msg('操作失败', {icon: 5});
            }
        });
    });
}
function sale(v) {
    var ids = getChks();
    if (ids == '') {
        WST.msg('请先选择商品!', {icon: 5});
        return;
    }
    layer.confirm((v == 1) ? "您确定要上架这些商品？" : "您确定要下架这些商品？", {icon: 3, title: '系统提示'}, function (tips) {
        var loading = layer.load('正在处理，请稍后...', 3);
        layer.close(tips);
        var params = {};
        params.ids = ids;
        params.isSale = v;
        $.post(Think.U('Home/Goods/sale'), params, function (data, textStatus) {
            layer.close(loading);
            var json = WST.toJson(data);
            if (json.status == '1') {
                WST.msg('操作成功！', {icon: 1}, function () {
                    location.reload();
                });
            } else if (json.status == '-2') {
                WST.msg('上架失败，请核对商品信息是否完整!', {icon: 5});
            } else if (json.status == '2') {
                WST.msg('已成功上架商品' + json.num + " 件，请核对未能上架的商品信息是否完整。", {icon: 5}, function () {
                    location.reload();
                });
            } else if (json.status == '-3') {
                WST.msg('上架商品失败!您的店铺权限不能出售商品，如有疑问请与商城管理员联系。', {icon: 5, time: 3000});
            } else {
                WST.msg('操作失败!', {icon: 5});
            }
        });
    });
}
function goodsSet(type, umark) {
    var ids = getChks();
    if (ids == '') {
        WST.msg('请先选择商品!', {icon: 5});
        return;
    }

    layer.load('正在处理，请稍后...', 3);
    var params = {};
    params.ids = ids;
    params.code = type;
    $.post(Think.U('Home/Goods/goodsSet'), params, function (data, textStatus) {
        var json = WST.toJson(data);
        if (json.status == '1') {
            WST.msg('操作成功！', {icon: 1}, function () {
                location.reload();
            });
        } else {
            WST.msg('操作失败!', {icon: 5});
        }
    });
}

function getShopCatListForGoods(v, id) {
    var params = {};
    params.id = v;
    $('#shopCatId2').empty();
    var html = [];
    $.post(Think.U('Home/ShopsCats/queryByList'), params, function (data, textStatus) {
        html.push('<option value="">请选择</option>');
        var json = WST.toJson(data);
        if (json.status == '1' && json.list) {
            var opts = null;
            for (var i = 0; i < json.list.length; i++) {
                opts = json.list[i];
                html.push('<option value="' + opts.catId + '" ' + ((id == opts.catId) ? 'selected' : '') + '>' + opts.catName + '</option>');
            }
        }
        $('#shopCatId2').html(html.join(''));
    });
}
$.fn.imagePreview = function (options) {
    var defaults = {};
    var opts = $.extend(defaults, options);
    var t = this;
    xOffset = 5;
    yOffset = 20;
    if (!$('#preview')[0])$("body").append("<div id='preview'><img width='200' src=''/></div>");
    $(this).hover(function (e) {
            $('#preview img').attr('src', domainURL + "/" + $(this).attr('img'));
            $("#preview").css("top", (e.pageY - xOffset) + "px").css("left", (e.pageX + yOffset) + "px").show();
        },
        function () {
            $("#preview").hide();
        });
    $(this).mousemove(function (e) {
        $("#preview").css("top", (e.pageY - xOffset) + "px").css("left", (e.pageX + yOffset) + "px");
    });
}
function getShopCatListForEdit(v, id) {
    var params = {};
    params.id = v;
    $('#shopCatId2').empty();
    if (v == 0) {
        $('#shopCatId2').html('<option value="">请选择</option>');
        return;
    }
    var html = [];
    $.post(Think.U('Home/ShopsCats/queryByList'), params, function (data, textStatus) {
        html.push('<option value="">请选择</option>');
        var json = WST.toJson(data);
        if (json.status == '1' && json.list) {
            var opts = null;
            for (var i = 0; i < json.list.length; i++) {
                opts = json.list[i];
                html.push('<option value="' + opts.catId + '" ' + ((id == opts.catId) ? 'selected' : '') + '>' + opts.catName + '</option>');
            }
        }
        $('#shopCatId2').html(html.join(''));
    });
}
function getBrands(catId) {
    var v = $('#brandId').attr('dataVal');
    var params = {};
    params.catId = catId;
    $('#brandId').empty();
    var html = [];
    $('#brandId').append('<option value="0">请选择</option>');
    $.post(Think.U('Home/Brands/queryBrandsByCat'), params, function (data, textStatus) {
        var json = WST.toJson(data);
        if (json.status == '1' && json.list) {
            for (var i = 0; i < json.list.length; i++) {
                opts = json.list[i];
                $('#brandId').append('<option value="' + opts.brandId + '" ' + ((v == opts.brandId) ? 'selected' : '') + '>' + opts.brandName + '</option>');
            }
        }
    })
}

function getCatListForEdit(objId, parentId, t, id) {
    var params = {};
    params.id = parentId;
    $('#' + objId).empty();
    if (t < 1) {
        $('#goodsCatId3').empty();
        $('#goodsCatId3').html('<option value="">请选择</option>');
        getBrands(parentId);
    }
    var html = [];
    $.post(Think.U('Home/GoodsCats/queryByList'), params, function (data, textStatus) {
        html.push('<option value="">请选择</option>');
        var json = WST.toJson(data);
        if (json.status == '1' && json.list) {
            var opts = null;
            for (var i = 0; i < json.list.length; i++) {
                opts = json.list[i];
                html.push('<option value="' + opts.catId + '" ' + ((id == opts.catId) ? 'selected' : '') + '>' + opts.catName + '</option>');
            }
        }
        $('#' + objId).html(html.join(''));
    });
}
function editAuctionGoods(menuId) {
    var params = {};
    params.id = $('#id').val();
    params.goodsSn = $('#goodsSn').val();
    params.goodsName = $('#goodsName').val();
    params.goodsImg = $('#goodsImg').val();
    params.goodsThumbs = $('#goodsThumbs').val();
    params.marketPrice = $('#marketPrice').val();
    params.shopPrice = $('#shopPrice').val();
    params.goodsStock = $('#goodsStock').val();
    params.brandId = $('#brandId').val();
    params.goodsUnit = $('#goodsUnit').val();
    params.goodsSpec = $('#goodsSpec').val();
    params.goodsCatId1 = $('#goodsCatId1').val();
    params.goodsCatId2 = $('#goodsCatId2').val();
    params.goodsCatId3 = $('#goodsCatId3').val();
    params.shopCatId1 = $('#shopCatId1').val();
    params.shopCatId2 = $('#shopCatId2').val();
    params.isSale = $('input[name="isSale"]:checked').val();
    params.isNew = $('input[name="isNew"]:checked').val();
    ;
    params.isBest = $('input[name="isBest"]:checked').val();
    ;
    params.isHot = $('input[name="isHot"]:checked').val();
    ;
    params.isRecomm = $('input[name="isRecomm"]:checked').val();
    ;
    params.goodsDesc = $('#goodsDesc').val();
    params.attrCatId = $('#attrCatId').val();
    params.goodsKeywords = $('#goodsKeywords').val();
    params.levelScore = $('#levelScore').val();
    params.costScore = $('#costScore').val();
    params.agentPrice = $('#agentPrice').val();

    params.auctionLowPrice = $('#auctionLowPrice').val();
    params.auctionAddPrice = $('#auctionAddPrice').val();
    params.auctionMinPrice = $('#auctionMinPrice').val();
    params.auctionMarginMoney = $('#auctionMarginMoney').val();
    params.auctionSecondTitle = $('#auctionSecondTitle').val();
    params.auctionStartTime = $('#auctionStartTime').val();
    params.auctionLabel = $('#auctionLabel').val();
    params.auctionEndTime = $('#auctionEndTime').val();
    params.auctionDesc = $('#auctionDesc').val();
    params.auctionWinNum = $('#auctionWinNum').val();

    if (params.attrCatId > 0) {
        WST.msg('多属性商品无法参加拍卖，请更改商品信息!', {icon: 5});
        return;
    }
    if (params.WinNum == '') {
        WST.msg('请输入中标人数!', {icon: 5});
        return;
    }
    if (params.auctionStartTime == '') {
        WST.msg('请输入开始时间!', {icon: 5});
        return;
    }
    if (params.auctionEndTime == '') {
        WST.msg('请输入结束时间!', {icon: 5});
        return;
    }
    if (params.auctionEndTime < params.auctionStartTime) {
        WST.msg('结束时间必须大于开始时间!', {icon: 5});
        return;
    }
    var time = Math.round(new Date().getTime() / 1000);
    if (params.auctionEndTime < time) {
        WST.msg('结束时间必须大于当前时间!', {icon: 5});
        return;
    }
    if (params.auctionDesc == '') {
        WST.msg('请输入活动说明!', {icon: 5});
        return;
    }


    if (params.attrCatId > 0) {

        params.goodsPriceNo = $('.hiddenPriceAttr').attr('dataNo');
        var priceCount = new Array()
        var isPriceRecomm = false;
        var stock = new Array();//每种价格属性库存
        for (var i = 0; i <= params.goodsPriceNo; i++) {
            params.priceAttrId = $.trim($('#priceAttr_' + i).val());
            priceCount.push(params.priceAttrId);
            if (document.getElementById('price_name_' + params.priceAttrId + '_' + i)) {
                params['price_name_' + params.priceAttrId + '_' + i] = $.trim($('#price_name_' + params.priceAttrId + '_' + i).val());
                params['price_price_' + params.priceAttrId + '_' + i] = $.trim($('#price_price_' + params.priceAttrId + '_' + i).val());
                params['price_isRecomm_' + params.priceAttrId + '_' + i] = $('#price_isRecomm_' + params.priceAttrId + '_' + i).prop('checked') ? 1 : 0;
                if (params['price_isRecomm_' + params.priceAttrId + '_' + i] == 1)isPriceRecomm++;
                params['price_stock_' + params.priceAttrId + '_' + i] = $.trim($('#price_stock_' + params.priceAttrId + '_' + i).val());
                //将每种价格属性分别求和
                if (!stock['stock_' + params.priceAttrId]) {
                    stock['stock_' + params.priceAttrId] = 0;
                }
                stock['stock_' + params.priceAttrId] += parseInt(params['price_stock_' + params.priceAttrId + '_' + i]);
                if (params['price_name_' + params.priceAttrId + '_' + i] == '') {
                    WST.msg('请输入商品规格！', {icon: 5});
                    $('#price_name_' + params.priceAttrId + '_' + i).focus();
                    return;
                }
                if (params['price_stock_' + params.priceAttrId + '_' + i] == '') {
                    WST.msg('请输入商品库存！', {icon: 5});
                    $('#price_stock_' + params.priceAttrId + '_' + i).focus();
                    return;
                }
            }

        }
        var stocks = 0;
        for (var p in stock) {
            if (!stocks) {
                stocks = stock[p];
            } else {
                if (stocks != stock[p]) {
                    WST.msg('每种价格属性库存总和必须一致', {icon: 5, time: 3000});
                    return;
                }
            }

        }
        var result = [], hash = {};
        for (var i = 0, elem; (elem = priceCount[i]) != null; i++) {
            if (!hash[elem]) {
                result.push(elem);
                hash[elem] = true;
            }
        }
        result.shift();
        if (isPriceRecomm != result.length) {
            WST.msg('请给每类价格属性选择推荐，以便展示！', {icon: 5, time: 3000});
            return;
        }
        $('.attrList').each(function () {
            //多选项处理
            if ($(this).attr('dataType') == 1) {
                var chk = [];
                $('input[name="attrTxtChk_' + $(this).attr('dataId') + '"]:checked').each(function () {
                    chk.push($(this).val())
                })
                params['attr_name_' + $(this).attr('dataId')] = chk.join(',');
            } else {
                //普通下拉，文本
                params['attr_name_' + $(this).attr('dataId')] = $.trim($(this).val());
            }
        });
    }

    var gallery = [];
    $('.gallery-img').each(function () {
        gallery.push($(this).attr('v') + '@' + $(this).attr('iv'));
    });
    if (params.goodsDesc == '') {
        WST.msg('请输入商品描述!', {icon: 5});
        return;
    }
    if (params.goodsImg == '') {
        WST.msg('请上传商品图片!', {icon: 5});
        return;
    }
    params.gallery = gallery.join(',');
    var loading = layer.load('正在提交商品信息，请稍后...', 3);
    $.post(Think.U('Home/Goods/editAuctionGoods'), params, function (data, textStatus) {
        layer.close(loading);
        var json = WST.toJson(data);
        console.log(json);
        if (json.status == '1') {
            WST.msg('操作成功!', {icon: 1}, function () {
                if ((menuId == 'toEditGoods')) {
                    location.href = Think.U('Home/Goods/toEdit', 'umark=toEditGoods');
                } else {
                    location.href = Think.U('Home/Goods/' + menuId);
                }
            });
        } else if (json.status == '-2') {
            if (params.isSale == 1) {
                WST.msg('您的店铺已被封，如有疑问请与商城管理员联系!', {icon: 5, time: 4000}, function () {
                    if ((menuId == 'toEditGoods')) {
                        location.href = Think.U('Home/Goods/toEdit', 'umark=toEditGoods');
                    } else {
                        location.href = Think.U('Home/Goods/' + menuId);
                    }
                });
            } else {
                WST.msg('操作成功!', {icon: 1}, function () {
                    if ((menuId == 'toEditGoods')) {
                        location.href = Think.U('Home/Goods/toEdit', 'umark=toEditGoods');
                    } else {
                        location.href = Think.U('Home/Goods/' + menuId);
                    }
                });
            }
        } else if (json.status == '-3') {
            if (params.isSale == 1) {
                WST.msg('您的店铺权限不能上架商品，所编辑商品已被存放在仓库中，如有疑问请与商城管理员联系!', {icon: 5, time: 4000}, function () {
                    if ((menuId == 'toEditGoods')) {
                        location.href = Think.U('Home/Goods/toEdit', 'umark=toEditGoods');
                    } else {
                        location.href = Think.U('Home/Goods/' + menuId);
                    }
                });
            } else {
                WST.msg('操作成功!', {icon: 1}, function () {
                    if ((menuId == 'toEditGoods')) {
                        location.href = Think.U('Home/Goods/toEdit', 'umark=toEditGoods');
                    } else {
                        location.href = Think.U('Home/Goods/' + menuId);
                    }
                });
            }
        } else {
            WST.msg('操作失败!', {icon: 5});
        }
    });
}


function editGoodsTest(type) {
    
    var isTrue = true;//判断数据是否填写
    var info = '';

    var isMiao = $('input:radio[name=isMiao]:checked').val();

    var agentPrice = $('#agentPrice').val();

    var applyTo = '';
    $("input[name=applyTo]").each(function () {
        if ($(this).prop('checked')) {
            if ($(this).attr("checked")) {
                applyTo += $(this).val() + ",";
            }
        }
    });
    applyTo = applyTo.substring(0, applyTo.length - 1);
    if (!isDefine(applyTo) || applyTo.length < 2) {
        WST.msg('请选择适用系统!', {icon: 5});
        return;
    }
    var game = $('#game option:selected').val();
    if (!isDefine(game) || game == 0) {
        WST.msg('请选择游戏!', {icon: 5});
        return;
    }
    var versions = $('#versions option:selected').val();
    if (!isDefine(versions) || versions == 0) {
        WST.msg('请选择游戏版本!', {icon: 5});
        return;
    }
    /**
     * @author peng	
     * @date 2017-01-01
     * @descreption 加上会员等级判断
     */
    
    var rank=$('#rank');
    
    if(rank.length==1){
        var rank_val=rank.val();
        if(rank_val==0){
            WST.msg('请选择会员等级!', {icon: 5});
            return;
        }
    }

    $('.lists').each(function () {
        var shopMoney = $(this).find('#shopPrice').val();
        
        if (!$(this).find('[name="is_renyi"]').prop('checked') && (!isDefine(shopMoney) || shopMoney <= 0)) {
            
            layer.tips('请填写充值面额', $(this).find('#shopPrice'), {
                tips: [1, '#ab774b'],
                time: 2000
            });
            isTrue = false;
            return false;
        }
        var shouChong = $(this).find('#shouChong').val();
        if (!isDefine(shouChong) || shouChong <= 0) {
            
            WST.msg('请填写首充号折后金额!', {icon: 5});
            isTrue = false;
            return false;
        }
        var daiChong = $(this).find('#daiChong').val();
        if (!isDefine(daiChong) || daiChong <= 0) {
            
            WST.msg('请填写首充号代充折后金额', {icon: 5});
            isTrue = false;
            return false;
        }

        var agentPrice = $(this).find('#agentPrice').val();
        if (!isDefine(agentPrice)) {
            agentPrice = 0;
        }

        //格式：充值面额:8.0,首充折后价格:8.0,代充折后价格：8.0,分销价格：1.0;
        info += 'shopPrice:' + shopMoney + ',' + 'shouChong:' + shouChong + ',' + 'daiChong:' + daiChong + ',' + 'agentPrice:' + agentPrice + ';';
    })
    if (isTrue == false) {
        WST.msg('请认真填写销售属性', {icon: 5});
        return;
        
    }
    /**
     * @author peng	
     * @date 2017-01-06
     * @descreption 会员价格
     */
    //var lmprice=parseFloat($('#low_member_price').val())
//    var lmprice1=parseFloat($('#low_member_price1').val())
//    var mmprice=parseFloat($('#mid_member_price').val())
//    var mmprice1=parseFloat($('#mid_member_price1').val())
//    var hmprice=parseFloat($('#heigh_member_price').val())
//    var hmprice1=parseFloat($('#heigh_member_price1').val())
//    var scval=parseFloat($('#shouChong').val())
//    var dcval=parseFloat($('#daiChong').val())
//    
//    
//    if(lmprice>scval || mmprice>scval || hmprice>scval){
//        WST.msg('会员价不可高于销售价', {icon: 5});
//        return;
//    }
//    if(lmprice1>dcval || mmprice1>dcval || hmprice1>dcval){
//        WST.msg('会员价不可高于销售价', {icon: 5});
//        return;
//    }
//    
//    if(lmprice<mmprice || lmprice<hmprice || mmprice<hmprice){
//        WST.msg('会员等级价格的设置不合理', {icon: 5});
//        return;
//    }
//    if(lmprice1<mmprice1 || lmprice1<hmprice1 || mmprice1<hmprice1){
//        WST.msg('会员等级价格的设置不合理', {icon: 5});
//        return;
//    }
    
    var params = {};
    params.isMiao = isMiao;
    params.agentPrice = agentPrice;
    params.applyTo = applyTo;
    params.game = game;
    params.versions = versions;
    params.info = info;
    
    var price_arr = new Array();
    var input_val;
    $('.priceList').not('.lists').each(function(){
        $(this).find('input').each(function(){
            
            input_val=$(this).val();
            
            price_arr.push(input_val?input_val:0)
        })
    })
    
    params.price = price_arr.join(',');
    
    if(rank.length==1){
        params.member_rank=rank_val;
    }
    
    var loading = layer.load('正在提交商品信息，请稍后...', 3);
    var postUrl = Think.U('Home/Goods/addGoods');
    if (type == 1) {
        postUrl = Think.U('Home/Goods/fxAddGoods');
    }
    
    $.post(postUrl, params, function (data, textStatus) {
        layer.close(loading);
        var json = WST.toJson(data);
        
        if (json.status == '1') {
        
            WST.msg('操作成功!', {icon: 1}, function () {
                location.href = Think.U('Home/Goods/add', 'umark=toEditGoods');
            });
        } else if (json.status == '-2') {
            if (params.isSale == 1) {
                WST.msg('您的店铺已被封，如有疑问请与商城管理员联系!', {icon: 5, time: 4000}, function () {
                    location.href = Think.U('Home/Goods/add', 'umark=toEditGoods');
                });
            }else if(rank.length==1){
                WST.msg('操作失败!', {icon: 1}, function () {
                    
                });
            }else {
                WST.msg('操作成功!', {icon: 1}, function () {
                    location.href = Think.U('Home/Goods/add', 'umark=toEditGoods');
                });
            }
        } else if (json.status == '-3') {
            if (params.isSale == 1) {
                WST.msg('您的店铺权限不能上架商品，所编辑商品已被存放在仓库中，如有疑问请与商城管理员联系!', {icon: 5, time: 4000}, function () {
                    location.href = Think.U('Home/Goods/add', 'umark=toEditGoods');
                });
            } else {
                WST.msg('操作成功!', {icon: 1}, function () {
                    location.href = Think.U('Home/Goods/add', 'umark=toEditGoods');
                });
            }
        }else if(json.status==-4){ //@author peng
            WST.msg('该版本商品已存在!', {
                icon: 5
            });
        }else if(json.status==-7){ //@author peng
            WST.msg('会员价要低于销售价', {
                icon: 5
            });
        }else if(json.status==-8){ //@author peng
            WST.msg('请设置完整的会员价', {
                icon: 5
            });
        }else if(json.status==-9){ //@author peng
            WST.msg('请设置合理的会员价', {
                icon: 5
            });
        }else {
            WST.msg('操作失败!', {
                icon: 5
            });
            
        }
        
    });


}


function editGoods(menuId) {
    
    var params = {};
    params.id = $('#id').val();
    params.goodsSn = $('#goodsSn').val();
    params.goodsName = $('#goodsName').val();
    params.goodsImg = $('#goodsImg').val();
    params.goodsThumbs = $('#goodsThumbs').val();
    params.marketPrice = $('#shopPrice').val();
    params.shopPrice = $('#shopPrice').val();
    params.goodsStock = $('#goodsStock').val();
    params.brandId = $('#brandId').val();
    params.goodsUnit = $('#goodsUnit').val();
    params.goodsSpec = $('#goodsSpec').val();
    params.isSale = $('input[name="isSale"]:checked').val();
    params.isMiao = $('input[name="isMiao"]:checked').val();
    params.isNew = $('input[name="isNew"]:checked').val();
    ;
    params.isBest = $('input[name="isBest"]:checked').val();
    ;
    params.isHot = $('input[name="isHot"]:checked').val();
    ;
    params.isRecomm = $('input[name="isRecomm"]:checked').val();
    params.goodsDesc = $('#goodsDesc').val();
    params.goodsKeywords = $('#goodsKeywords').val();
    params.agentPrice = $('#agentPrice').val();
    params.scope = $('#scope').val();
    params.gameId = $('#game').val();
    
    if (params.scope == 0) {
        WST.msg('请选择游戏类型!', {icon: 5});
        return;
    }
    if (params.game == 0) {
        WST.msg('请选择所属游戏!', {icon: 5});
        return;
    }
    var info = '';
    var is_wrong=0;
    
    $('.versions_price').each(function (index) {
        
        var price = parseFloat($.trim($(this).val()));
        
        var id = $(this).attr('data-id');
        var vid = $(this).attr('data-vid');
        //会员价格
        var ptr=$(this).closest('tr')
        var price1=parseFloat(ptr.find('.price1').val());
        var price2=parseFloat(ptr.find('.price2').val());
        var price3=parseFloat(ptr.find('.price3').val());
        price1 = isNaN(price1)?0:price1;
        price2 = isNaN(price2)?0:price2;
        price3 = isNaN(price3)?0:price3;
        if((price1+price2+price3) > 0){
            if(price1==0 || price2==0 || price3==0){
                is_wrong=3;
                return false;
            }
            if(price<=price1 || price<=price2 | price<=price3){
                is_wrong=1;
                return false;
            }
            
            if(price1<=price2 || price1<=price2 || price2<=price3){
                is_wrong=2;
                return false;
            }
        
        }
        
        if (price > 0) {
            info += id + '|' + vid + '|' + price + '|' +price1+ '|' +price2+ '|' +price3 + ';';
        }
    })
    if(is_wrong==1){
        WST.msg('会员价格不可高于销售价!', {icon: 5});
        return;
    }else if(is_wrong==2){
        WST.msg('会员等级价格的设置不合理', {icon: 5});
        return;
    }
    else if(is_wrong==3){
        WST.msg('请设置好完整等级的会员价', {icon: 5});
        return;
    }
    if (info.length < 2) {
        WST.msg('请认真填写销售属性!', {icon: 5});
        return;
    }
    params.info = info;

    var appTo = '';
    $('.applyTo').each(function () {
        if ($(this).is(':checked')) {
            appTo += $(this).val() + ',';
        }
    })
    if (appTo.length < 2) {
        WST.msg('请选择适用平台!', {icon: 5});
        return;
    }
    params.appTo = appTo;
    var gallery = [];
    $('.gallery-img').each(function () {
        gallery.push($(this).attr('v') + '@' + $(this).attr('iv'));
    });
    if (params.goodsDesc == '') {
        WST.msg('请输入商品描述!', {icon: 5});
        return;
    }
    if (params.goodsImg == '') {
        WST.msg('请上传商品图片!', {icon: 5});
        return;
    }
    params.gallery = gallery.join(',');
    var loading = layer.load('正在提交商品信息，请稍后...', 3);
    $.post(Think.U('Home/Goods/edit'), params, function (data, textStatus) {
        layer.close(loading);
        var json = WST.toJson(data);
        console.log(json);
        if (json.status == '1') {
            WST.msg('操作成功!', {icon: 1}, function () {
                if ((menuId == 'toEditGoods')) {
                    location.href = Think.U('Home/Goods/toEdit', 'umark=toEditGoods');
                } else {
                    location.href = Think.U('Home/Goods/' + menuId);
                }
            });
        } else if (json.status == '-2') {
            if (params.isSale == 1) {
                WST.msg('您的店铺已被封，如有疑问请与商城管理员联系!', {icon: 5, time: 4000}, function () {
                    if ((menuId == 'toEditGoods')) {
                        location.href = Think.U('Home/Goods/toEdit', 'umark=toEditGoods');
                    } else {
                        location.href = Think.U('Home/Goods/' + menuId);
                    }
                });
            } else {
                WST.msg('操作成功!', {icon: 1}, function () {
                    if ((menuId == 'toEditGoods')) {
                        location.href = Think.U('Home/Goods/toEdit', 'umark=toEditGoods');
                    } else {
                        location.href = Think.U('Home/Goods/' + menuId);
                    }
                });
            }
        } else if (json.status == '-3') {
            if (params.isSale == 1) {
                WST.msg('您的店铺权限不能上架商品，所编辑商品已被存放在仓库中，如有疑问请与商城管理员联系!', {icon: 5, time: 4000}, function () {
                    if ((menuId == 'toEditGoods')) {
                        location.href = Think.U('Home/Goods/toEdit', 'umark=toEditGoods');
                    } else {
                        location.href = Think.U('Home/Goods/' + menuId);
                    }
                });
            } else {
                WST.msg('操作成功!', {icon: 1}, function () {
                    if ((menuId == 'toEditGoods')) {
                        location.href = Think.U('Home/Goods/toEdit', 'umark=toEditGoods');
                    } else {
                        location.href = Think.U('Home/Goods/' + menuId);
                    }
                });
            }
        } else {
            WST.msg('操作失败!', {
                icon: 5
            });
        }
    });
}
function getAttrList(catId) {
    $('#priceContainer').hide();
    $('#attrContainer').hide();
    $('#priceConent').empty();
    $('#attrConent').empty();
    if (catId == 0) {
        $('.hiddenPriceAttr').attr('dataId', 0);
        $('.hiddenPriceAttr').attr('dataNo', 0);
        $('.hiddenPriceAttr').val('');

    }
    $.post(Think.U('Home/Attributes/getAttributes'), {catId: catId}, function (data, textStatus) {
        var json = WST.toJson(data);
        var priceAttr = new Array();
        if (json.status == '1' && json.list) {
            var opts = null;
            for (var i = 0; i < json.list.length; i++) {
                opts = json.list[i];
                if (opts.isPriceAttr == 1) {
                    priceAttr[i] = opts;
                } else {
                    addAttr(opts);
                    $('#attrContainer').show();
                }
            }
        }

        if (priceAttr) {
            //全局变量，商品_属性id
            window.dataId = 0;
            window.dataAll = 0;
            for (var p in priceAttr) {
                if (p) {
                    window.dataAll++;
                }
                //！取消数组
                var dataId = new Array();
                dataId[p] = priceAttr[p].attrId
                var attrName = new Array();
                attrName[p] = priceAttr[p].attrName
                $('.hiddenPriceAttr').attr('dataId', dataId[p]);
                $('.hiddenPriceAttr').attr('dataNo', window.dataId);
                $('.hiddenPriceAttr').attr('dataAll', window.dataAll);
                $('.hiddenPriceAttr').val(attrName[p]);
                addPriceAttr();
                $('#priceContainer').show();

                window.dataId++;
            }
        }
    });
}
function addPriceAttr(attrId, attrName, flag) {
    var goodsPriceNo = $('.hiddenPriceAttr').attr('dataNo');
    goodsPriceNo++;
    var goodsAll = $('.hiddenPriceAttr').attr('dataAll');
    var k = $('#prcieattr').val();
    console.log(k);
    if (k) {
        goodsAll = k;
    }
    if (attrId == null) {
        var obj = {attrId: $('.hiddenPriceAttr').attr('dataId'), attrName: $('.hiddenPriceAttr').val()};
    } else {
        var obj = {attrId: attrId, attrName: attrName};
    }
    var html = [];
    html.push('<tr id="attr_' + goodsPriceNo + '"><td style="text-align:right">' + obj.attrName + '：<input type="hidden" id="priceAttr_' + goodsPriceNo + '" value="' + obj.attrId + '" /></td>');
    html.push('<td><input type="text" id="price_name_' + obj.attrId + '_' + goodsPriceNo + '" /></td>');
    html.push('<td><input type="text" id="price_price_' + obj.attrId + '_' + goodsPriceNo + '" value="0" onblur="checkAttPrice(' + obj.attrId + ',' + goodsPriceNo + ');" onkeypress="return WST.isNumberdoteKey(event)" onkeyup="javascript:WST.isChinese(this,1)" maxLength="10"/></td>');

    html.push('<td><input type="radio" onclick="checkAttPriceSk(' + obj.attrId + ',' + goodsPriceNo + ');" id="price_isRecomm_' + obj.attrId + '_' + goodsPriceNo + '" name="price_isRecomm_' + obj.attrId + '"/></td>');
    html.push('<td><input type="text" id="price_stock_' + obj.attrId + '_' + goodsPriceNo + '" onblur="getTstock(' + obj.attrId + ');" value="100" onkeypress="return WST.isNumberKey(event)" onblur="javascript:statGoodsStaock()" onkeyup="javascript:WST.isChinese(this,1)" maxLength="25"/></td>');

    if (goodsPriceNo == goodsAll) {
        html.push('<td><a title="新增" class="add btn" href="javascript:addPriceAttr(' + obj.attrId + ',' + '\'' + obj.attrName + '\'' + ',' + goodsPriceNo + ')"></a></td>');
    } else {
        html.push('<td><a title="删除" class="del btn" href="javascript:delPriceAttr(' + goodsPriceNo + ')"></a></td></tr>');
    }
    var flag = arguments[2] ? arguments[2] : '';
    $('.hiddenPriceAttr').attr('dataNo', goodsPriceNo);
    if (typeof(flag) == 'number') {
        if (flag == goodsAll) {
            $('#priceConent').append(html.join(''));
        } else {
            var s = $('#priceattr_' + obj.attrId).val();//编辑商品价格属性
            if (s) {
                flag += parseInt(s);
            } else {
                flag++;
            }
            $(html.join('')).insertBefore('#attr_' + flag);
        }
    } else {
        $('#priceConent').append(html.join(''));
    }
    statGoodsStaock();
    getTstock();
}
//设置推荐价格时，修改商品信息的店铺价格
function checkAttPrice(attrId, goodsPriceNo) {
    //全局变量，价格属性的个数
    var price = 0;
    goodsPriceNo = $('.hiddenPriceAttr').attr('dataNo');
    //遍历每个价格属性
    for (var i = 0; i <= goodsPriceNo; i++) {
        priceAttrId = $.trim($('#priceAttr_' + i).val());
        if ($("#price_isRecomm_" + priceAttrId + "_" + i).prop('checked')) {
            window.priceCount++;
            price += parseFloat($.trim($("#price_price_" + priceAttrId + "_" + i).val()));
        }
    }
    $("#shopPrice").val(price);
}
//设置推荐价格时，修改商品信息的店铺价格
function checkAttPriceSk(attrId, goodsPriceNo) {
    //全局变量，价格属性的个数
    var price = 0;
    goodsPriceNo = $('.hiddenPriceAttr').attr('dataNo');
    //遍历每个价格属性
    for (var i = 0; i <= goodsPriceNo; i++) {
        priceAttrId = $.trim($('#priceAttr_' + i).val());
        if ($("#price_isRecomm_" + priceAttrId + "_" + i).prop('checked')) {
            window.priceCount++;
            price += parseFloat($.trim($("#price_price_" + priceAttrId + "_" + i).val()));
        }
    }
    $("#shopPrice").val(price);

    var priceSk = 0;
    goodsPriceNo = $('.hiddenPriceAttr').attr('dataNo');
    //遍历每个价格属性
    for (var i = 0; i <= goodsPriceNo; i++) {
        priceAttrId = $.trim($('#priceAttr_' + i).val());
        if ($("#price_isRecomm_" + priceAttrId + "_" + i).prop('checked')) {
            window.priceCount++;
            priceSk += parseFloat($.trim($("#sk_price_price_" + priceAttrId + "_" + i).val()));
        }
    }
    $("#seckillPrice").val(priceSk);
}


function delPriceAttr(v) {
    $('#attr_' + v).remove();
    statGoodsStaock();
    getTstock();
}
//修改了价格属性库存
function getTstock(attrId) {
    var tstock = 0;
    $("input[id^=price_stock_" + attrId + "]").each(function () {
        tstock = tstock + parseInt($(this).val());
    });
    if (!isNaN(tstock)) {
        $("#goodsStock").val(tstock);
    }
}
//修改了价格属性库存秒杀专用 0504
function getSkStock(attrId) {
    var tstock = 0;
    $("input[id^=sk_price_stock_" + attrId + "]").each(function () {
        tstock = tstock + parseInt($(this).val());
    });
    if (!isNaN(tstock)) {
        $("#seckillStock").val(tstock);
    }
}
//默认的价格属性库存
function statGoodsStaock() {
    var goodsPriceNo = $('.hiddenPriceAttr').attr('dataNo');
    var attrId = $('.hiddenPriceAttr').attr('dataId');
    var totalStock = 0;
    for (var i = 0; i <= goodsPriceNo; i++) {
        if (document.getElementById('price_stock_' + attrId + "_" + i)) {
            totalStock = totalStock + parseInt($.trim($('#price_stock_' + attrId + '_' + i).val()), 10);
        }
    }
    $('#goodsStock').val(totalStock);
}
//默认的价格属性库存 0504
function statGoodsStaockSk() {
    var goodsPriceNo = $('.hiddenPriceAttr').attr('dataNo');
    var attrId = $('.hiddenPriceAttr').attr('dataId');
    var totalStock = 0;
    for (var i = 0; i <= goodsPriceNo; i++) {
        if (document.getElementById('sk_price_stock_' + attrId + "_" + i)) {
            totalStock = totalStock + parseInt($.trim($('#sk_price_stock_' + attrId + '_' + i).val()), 10);
        }
    }
    $('#seckillStock').val(totalStock);
}
function addAttr(obj) {
    var html = [];
    html.push("<tr id='attr_" + obj.attrId + "'>");
    html.push("<th style='width:80px;text-align:right' nowrap>" + obj.attrName + "：</th><td>");
    if (obj.attrType == 0) {
        html.push("<input type='text' style='width:70%;' dataId='" + obj.attrId + "' class='attrList'/>");
    } else if (obj.attrType == 1) {
        if (obj.opts && obj.opts.txt) {
            html.push("<input type='hidden' dataType='" + obj.attrType + "' dataId='" + obj.attrId + "' class='attrList'>");
            for (var i = 0; i < obj.opts.txt.length; i++) {
                html.push("<label><input type='checkbox' name='attrTxtChk_" + obj.attrId + "' value='" + obj.opts.txt[i] + "'/>" + obj.opts.txt[i] + "</label>&nbsp;&nbsp;");
            }
        }
        html.push("</select>");
    } else if (obj.attrType == 2) {
        html.push("<select class='attrList' id='attr_name_" + obj.attrId + "' dataId='" + obj.attrId + "'>");
        if (obj.opts && obj.opts.txt) {
            for (var i = 0; i < obj.opts.txt.length; i++) {
                html.push("<option value='" + obj.opts.txt[i] + "'>" + obj.opts.txt[i] + "</option>");
            }
        }
        html.push("</select>");
    }
    html.push("</td></tr>");
    $('#attrConent').append(html.join(''));
}

/*****************************商品分类***********************************/
function editGoodsCat() {
    var params = {};
    params.id = $('#id').val();
    params.parentId = $('#parentId').val();
    params.catName = $('#catName').val();
    params.isShow = $('input[name="isShow"]:checked').val();
    ;
    params.catSort = $('#catSort').val();
    var loading = layer.load('正在提交商品分类信息，请稍后...', 3);
    $.post(Think.U('Home/ShopsCats/edit'), params, function (data, textStatus) {
        layer.close(loading);
        var json = WST.toJson(data);
        if (json.status == '1') {
            WST.msg('操作成功!', {icon: 1}, function () {
                location.href = Think.U('Home/ShopsCats/index');
            });
        } else {
            layer.msg('操作失败!', {icon: 5});
        }
    });
}
function delGoodsCat(id) {
    layer.confirm("您确定要删除该商品分类吗？", {icon: 3, title: '系统提示'}, function (tips) {
        layer.load('正在处理，请稍后...', 3);
        layer.close(tips);
        $.post(Think.U('Home/ShopsCats/del'), {id: id}, function (data, textStatus) {
            var json = WST.toJson(data);
            if (json.status == '1') {
                WST.msg('操作成功!', {icon: 1}, function () {
                    location.reload();
                });
            } else {
                WST.msg('操作失败!', {icon: 5});
            }
        });
    })
}
function editGoodsCatName(obj) {
    var name = $('#');
    $.post(Think.U('Home/ShopsCats/editName'), {
        id: $(obj).attr('dataId'),
        catName: obj.value
    }, function (data, textStatus) {
        var json = WST.toJson(data);
        if (json.status == '1') {
            WST.msg('操作成功!', {icon: 1, time: 500});
        } else {
            WST.msg('操作失败!', {icon: 5});
        }
    });
}
function editGoodsCatSort(obj) {
    var name = $('#');
    $.post(Think.U('Home/ShopsCats/editSort'), {
        id: $(obj).attr('dataId'),
        catSort: obj.value
    }, function (data, textStatus) {
        var json = WST.toJson(data);
        if (json.status == '1') {
            WST.msg('操作成功!', {icon: 1, time: 500});
        } else {
            WST.msg('操作失败!', {icon: 5});
        }
    });
}
function loadGoodsCatChildTree(obj, pid, objId) {
    var showhtml = "<span class='wst-state_yes'></span>";
    var hidehtml = "<span class='wst-state_no'></span>";
    var str = objId.split("_");
    level = (str.length - 2);
    if ($(obj).hasClass('wst-tree-open')) {
        $(obj).removeClass('wst-tree-open').addClass('wst-tree-close');
        $('tr[class^="' + objId + '"]').hide();
    } else {
        $(obj).removeClass('wst-tree-close').addClass('wst-tree-open');
        $('tr[class^="' + objId + '"]').show();
    }
}
/*****************商品评价**************************/
function queryAppraises() {
    var shopCatId1 = $('#shopCatId1').val();
    var shopCatId2 = $('#shopCatId2').val();
    var goodsName = $('#goodsName').val();
    location.href = Think.U('Home/GoodsAppraises/index', 'umark=GoodsAppraises&goodsName=' + goodsName + "&shopCatId1=" + shopCatId1 + "&shopCatId2=" + shopCatId2);
}
function getShopCatListForAppraises(v, id) {
    var params = {};
    params.id = v;
    $('#shopCatId2').empty();
    var html = [];
    $.post(Think.U('Home/ShopsCats/queryByList'), params, function (data, textStatus) {
        html.push('<option value="">请选择</option>');
        var json = WST.toJson(data);
        if (json.status == '1' && json.list) {
            var opts = null;
            for (var i = 0; i < json.list.length; i++) {
                opts = json.list[i];
                html.push('<option value="' + opts.catId + '" ' + ((id == opts.catId) ? 'selected' : '') + '>' + opts.catName + '</option>');
            }
        }
        $('#shopCatId2').html(html.join(''));
    });
}
/******************订单列表**********************/
//查看订单
function showOrder(id) {
     /**
     * @author peng	
     * @date 2017-01-04
     * @descreption 是平台商品的情况
     */
    var url;
    
    if(is_pt==1){
        
        url=[Think.U('Home/Orders/getOrderDetails', 'orderId=' + id+'&is_pt=1')];
    }else{
        url=[Think.U('Home/Orders/getOrderDetails', 'orderId=' + id)];
    }
    
    layer.open({
        type: 2,
        title: "订单详情",
        shade: [0.6, '#000'],
        border: [0],
        content:url,
        area: ['1020px', ($(window).height() - 50) + 'px']
    });
}
//查看发货
function fahuo(id) {
    /**
     * @author peng	
     * @date 2017-01-04
     * @descreption 是平台商品的情况
     */
    var url;
    
    if(is_pt==1){
        
        url=[Think.U('Home/Orders/fahuo', 'orderId=' + id+'&is_pt=1')]
    }else{
        url=[Think.U('Home/Orders/fahuo', 'orderId=' + id)];
    }
    layer.open({
        type: 2,
        title: "发货处理",
        shade: [0.6, '#000'],
        border: [0],
        content: url,
        area: ['1020px', ($(window).height() - 50) + 'px']
    });
}

/**
 * @author peng
 * @copyright 2016
 * @remark 自动发货
 */
function auto_fahuo(id) {
    /**
     * @author peng	
     * @date 2017-01-04
     * @descreption 是平台商品的情况
     */
    var url;
    
    if(is_pt==1){
        url=[Think.U('Home/Orders/fahuo', 'orderId=' + id + '&auto=1&is_pt=1')];
        
    }else{
        url=[Think.U('Home/Orders/fahuo', 'orderId=' + id + '&auto=1')];
    }
    
    layer.open({
        type: 2,
        title: "发货处理",
        shade: [0.6, '#000'],
        border: [0],
        content: url,
        area: ['1020px', ($(window).height() - 50) + 'px']
    });
}

//受理
function shopOrderAccept(id) {
    layer.confirm('您确定受理该订单吗？', {icon: 3, title: '系统提示'}, function (tips) {
        var ll = layer.load('数据处理中，请稍候...');
        $.post(Think.U('Home/Orders/shopOrderAccept'), {orderId: id}, function (data) {
            layer.close(ll);
            layer.close(tips);
            var json = WST.toJson(data);
            if (json.status > 0) {
                $(".wst-tab-nav").find("li").eq(statusMark).click();
            } else if (json.status == -1) {
                WST.msg('操作失败，订单状态已发生改变，请刷新后再重试 !', {icon: 5});
            } else {
                WST.msg('操作失败，请与商城管理员联系 !', {icon: 5});
            }
        });
    });
}
//批量受理
function batchShopOrderAccept() {
    var ids = WST.getChks('.chk_0');
    ids = ids.join(',');
    if (ids == '') {
        WST.msg('请选择要受理的订单 !', {icon: 5});
        return;
    }
    layer.confirm('您确定受理这些订单吗？', {icon: 3, title: '系统提示'}, function (tips) {
        var ll = layer.load('数据处理中，请稍候...');
        $.post(Think.U('Home/Orders/batchShopOrderAccept'), {orderIds: ids}, function (data) {
            layer.close(ll);
            layer.close(tips);
            var json = WST.toJson(data);
            if (json.status > 0) {
                $(".wst-tab-nav").find("li").eq(statusMark).click();
            } else if (json.status == -1) {
                WST.msg('操作失败，订单状态已发生改变，请刷新后再重试 !', {icon: 5});
            } else if (json.status == -2) {
                WST.msg('操作完成，部分订单状态已发生改变，请注意核对订单状态 !', {icon: 5}, function () {
                    $(".wst-tab-nav").find("li").eq(statusMark).click();
                });
            } else {
                WST.msg('操作失败，请与商城管理员联系 !', {icon: 5});
            }
        });
    });
}
//打包
function shopOrderProduce(id) {
    layer.confirm('您确定打包该商品吗？', {icon: 3, title: '系统提示'}, function (tips) {
        var ll = layer.load('数据处理中，请稍候...');
        $.post(Think.U('Home/Orders/shopOrderProduce'), {orderId: id}, function (data) {
            layer.close(ll);
            layer.close(tips);
            var json = WST.toJson(data);
            if (json.status > 0) {
                $(".wst-tab-nav").find("li").eq(statusMark).click();
            } else if (json.status == -1) {
                WST.msg('操作失败，订单状态已发生改变，请刷新后再重试 !', {icon: 5});
            } else {
                WST.msg('操作失败，请与商城管理员联系 !', {icon: 5});
            }
        });
    });
}
//批量打包
function batchShopOrderProduce() {
    var ids = WST.getChks('.chk_1');
    ids = ids.join(',');
    if (ids == '') {
        WST.msg('请选择要打包的订单 !', {icon: 5});
        return;
    }
    layer.confirm('您确定打包这些商品吗？', {icon: 3, title: '系统提示'}, function (tips) {
        var ll = layer.load('数据处理中，请稍候...');
        $.post(Think.U('Home/Orders/batchShopOrderProduce'), {orderIds: ids}, function (data) {
            layer.close(ll);
            layer.close(tips);
            var json = WST.toJson(data);
            if (json.status > 0) {
                $(".wst-tab-nav").find("li").eq(statusMark).click();
            } else if (json.status == -1) {
                WST.msg('操作失败，订单状态已发生改变，请刷新后再重试 !', {icon: 5});
            } else if (json.status == -2) {
                WST.msg('操作完成，部分订单状态已发生改变，请注意核对订单状态 !', {icon: 5}, function () {
                    $(".wst-tab-nav").find("li").eq(statusMark).click();
                });
            } else {
                WST.msg('操作失败，请与商城管理员联系 !', {icon: 5});
            }
        });
    });
}
//选择配送方式
function shopOrderDeliveryType(id) {
    var html = new Array();
    html.push('<div style="margin:0px;pading:10px;background-color:#999;border-radius:2px;height:20px;">选择配送方式</div>');
    html.push('<div style="text-align:center;margin-top:50px;">');
    html.push('<button class="wst-btn-delivery" type="submit" onclick="javascript:shopOrderDelivery(' + id + ',' + '0' + ')">商城配送</button>');
    html.push('<button class="wst-btn-delivery" type="submit" onclick="javascript:deliveryExpress(' + id + ')">物流配送</button></a>');
    html.push('</div>');
    html.push('<div style="margin:28px 50px;float:right;"><button class="wst-btn-query" onclick="javascript:cancelDelivery()">取消</button></div>')
    $('#deliveryType').show();
    $('#deliveryType').html(html.join(""));
}
//取消选择配送方式
function cancelDelivery() {
    $('#deliveryType').hide();
    $('#deliveryExpress').hide();
}
//获取配送信息
function deliveryExpress(id) {
    $.post(Think.U('Home/Express/get'), {orderId: id}, function (data) {
        var json = WST.toJson(data);
        if (json == 0) {
            WST.msg('操作失败，请先在物流管理中增加物流信息 !', {icon: 5});
        } else {
            var html = new Array();
            html.push('<div style="margin:0px;pading:10px;background-color:#999;border-radius:2px;height:20px;">填写物流信息</div>');
            html.push('<div style="margin-top:20px;margin-left:30px;">物流名称：<select id="expressCompany" style="border: 1px solid #ccc;border-radius: 4px;box-shadow: 2px 2px 2px #f0f0f0 ;margin:3px;">')
            for (var i = 0; i < json.length; i++) {
                html.push('<option value="' + json[i]['id'] + '">' + json[i]['expressCompany'] + '</option>');
            }
            html.push('</select><br/>');
            html.push('物流单号：<input type="text" id="trackNumber" width="200"/></div>')
            html.push('<div style="margin-top:20px;text-align:center">')
            html.push('<button class="wst-btn-query" onclick="javascript:shopOrderDelivery(' + id + ',' + '1' + ')">确认</button>');
            html.push('<button class="wst-btn-query" onclick="javascript:cancelDelivery()">取消</button></div>');
            $('#deliveryType').hide();
            $('#deliveryExpress').html(html.join(""));
            $('#deliveryExpress').show();
        }
    });
}
//发货配送
function shopOrderDelivery(id, deliveryType) {
    $('#deliveryType').hide();
    $('#deliveryExpress').hide();
    var param = {};
    if (deliveryType == 1) {
        param.expressId = $('#expressCompany').val();
        param.trackNumber = $('#trackNumber').val();
    }
    param.deliveryType = deliveryType;
    param.orderId = id;
    //layer.confirm('确定正在发货吗？',{icon: 3, title:'系统提示'}, function(tips){
    var ll = layer.load('数据处理中，请稍候...');
    $.post(Think.U('Home/Orders/shopOrderDelivery'), param, function (data) {
        layer.close(ll);
        var json = WST.toJson(data);
        if (json.status > 0) {
            $(".wst-tab-nav").find("li").eq(statusMark).click();
        } else if (json.status == -1) {
            WST.msg('操作失败，订单状态已发生改变，请刷新后再重试 !', {icon: 5});
        } else {
            WST.msg('操作失败，请与商城管理员联系 !', {icon: 5});
        }
    });
    //});
}
//批量发货配送
function batchShopOrderDelivery(id) {
    var ids = WST.getChks('.chk_2');
    ids = ids.join(',');
    if (ids == '') {
        WST.msg('请选择要发货的订单 !', {icon: 5});
        return;
    }
    layer.confirm('您确定这些订单正在发货吗？', {icon: 3, title: '系统提示'}, function (tips) {
        var ll = layer.load('数据处理中，请稍候...');
        $.post(Think.U('Home/Orders/batchShopOrderDelivery'), {orderIds: ids}, function (data) {
            layer.close(ll);
            layer.close(tips);
            var json = WST.toJson(data);
            if (json.status > 0) {
                $(".wst-tab-nav").find("li").eq(statusMark).click();
            } else if (json.status == -1) {
                WST.msg('操作失败，订单状态已发生改变，请刷新后再重试 !', {icon: 5});
            } else if (json.status == -2) {
                WST.msg('操作完成，部分订单状态已发生改变，请注意核对订单状态 !', {icon: 5}, function () {
                    $(".wst-tab-nav").find("li").eq(statusMark).click();
                });
            } else {
                WST.msg('操作失败，请与商城管理员联系 !', {icon: 5});
            }
        });
    });
}
//确认收货
function shopOrderReceipt(id) {
    layer.confirm('确定已收货吗？', {icon: 3, title: '系统提示'}, function (tips) {
        var ll = layer.load('数据处理中，请稍候...');
        $.post(Think.U('Home/Orders/shopOrderReceipt'), {orderId: id}, function (data) {
            layer.close(ll);
            layer.close(tips);
            var json = WST.toJson(data);
            if (json.status > 0) {
                $(".wst-tab-nav").find("li").eq(statusMark).click();
            } else if (json.status == -1) {
                WST.msg('操作失败，订单状态已发生改变，请刷新后再重试 !', {icon: 5});
            } else {
                WST.msg('操作失败，请与商城管理员联系 !', {icon: 5});
            }
        });
    });

}
//取消团购订单
function shopOrderCancel(id, type, shop) {
    if (type == 1 || type == 2) {
        var w = layer.open({
            type: 1,
            title: "取消原因",
            shade: [0.6, '#000'],
            border: [0],
            content: '<textarea id="rejectionRemarks" rows="8" style="width:96%" maxLength="100"></textarea>',
            area: ['500px', '250px'],
            btn: ['提交', '关闭窗口'],
            yes: function (index, layero) {
                var rejectionRemarks = $.trim($('#rejectionRemarks').val());
                if (rejectionRemarks == '') {
                    WST.msg('请输入拒收原因 !', {icon: 5});
                    return;
                }
                var ll = layer.load('数据处理中，请稍候...');
                $.post(Think.U('Home/Orders/orderCancel'), {
                    orderId: id,
                    type: 1,
                    rejectionRemarks: rejectionRemarks
                }, function (data) {
                    layer.close(w);
                    layer.close(ll);
                    var json = WST.toJson(data);
                    if (json.status > 0) {
                        window.location.reload();
                    } else if (json.status == -1) {
                        WST.msg('操作失败，订单状态已发生改变，请刷新后再重试 !', {icon: 5});
                    } else {
                        WST.msg('操作失败，请与商城管理员联系 !', {icon: 5});
                    }
                });
            }
        });
    } else {
        layer.confirm('您确定要取消该订单吗？', {icon: 3, title: '系统提示'}, function (tips) {
            var ll = layer.load('数据处理中，请稍候...');
            var url = "Home/Orders/shopOrderCancel";
            $.post(Think.U(url), {orderId: id}, function (data) {
                layer.close(ll);
                layer.close(tips);
                var json = WST.toJson(data);
                console.log(json);
                if (json.status > 0) {
                    window.location.reload();
                } else if (json.status == -1) {
                    WST.msg('操作失，订单状态已发生改变，请刷新后再重试 !', {icon: 5});
                } else {
                    WST.msg('操作失，请与商城管理员联系 !', {icon: 5});
                }
            });
        });
    }
}
//同意拒收
function shopOrderRefund(id, type) {
    if (type == 1) {
        layer.confirm('您同意取消/拒收该订单吗？', {icon: 3, title: '系统提示'}, function (tips) {
            var ll = layer.load('数据处理中，请稍候...');
            $.post(Think.U('Home/Orders/shopOrderRefund'), {orderId: id, type: type}, function (data) {
                layer.close(ll);
                layer.close(tips);
                var json = WST.toJson(data);
                if (json.status > 0) {
                    $(".wst-tab-nav").find("li").eq(statusMark).click();
                } else if (json.status == -1) {
                    WST.msg('操作失败，订单状态已发生改变，请刷新后再重试 !', {icon: 5});
                } else {
                    WST.msg('操作失败，请与商城管理员联系 !', {icon: 5});
                }
            });
        });
    } else {
        var w = layer.open({
            type: 1,
            title: "不同意原因",
            shade: [0.6, '#000'],
            border: [0],
            content: '<textarea id="rejectionRemarks" rows="8" style="width:96%" maxLength="100"></textarea>',
            area: ['500px', '250px'],
            btn: ['提交', '关闭窗口'],
            yes: function (index, layero) {
                var rejectionRemarks = $.trim($('#rejectionRemarks').val());
                if (rejectionRemarks == '') {
                    WST.msg('请输入拒收原因 !', {icon: 5});
                    return;
                }
                var ll = layer.load('数据处理中，请稍候...');
                $.post(Think.U('Home/Orders/shopOrderRefund'), {
                    orderId: id,
                    type: type,
                    rejectionRemarks: rejectionRemarks
                }, function (data) {
                    layer.close(w);
                    layer.close(ll);
                    var json = WST.toJson(data);
                    if (json.status > 0) {
                        $(".wst-tab-nav").find("li").eq(statusMark).click();
                    } else if (json.status == -1) {
                        WST.msg('操作失败，订单状态已发生改变，请刷新后再重试 !', {icon: 5});
                    } else {
                        WST.msg('操作失败，请与商城管理员联系 !', {icon: 5});
                    }
                });
            }
        });
    }
}
function queryOrderPager(statusMark, pcurr, id, skId) {

    var param = {};
    param.orderNo = $.trim($("#orderNo_" + statusMark).val());
    param.gameName = $.trim($("#gameName_" + statusMark).val());
    param.versions = $.trim($("#versions_" + statusMark).val());
    param.endDay = $.trim($("#endDay_" + statusMark).val());
    param.starDay = $.trim($("#starDay_" + statusMark).val());
    param.statusMark = statusMark;
    param.pcurr = pcurr;
    /**
     * @author peng	
     * @date 2017-01-04
     * @descreption 加上平台的情况
     */
     param.is_pt=is_pt;
    var ll = layer.load('数据加载中，请稍候...');
    $.post(Think.U('Home/Orders/queryShopOrders'), param, function (data, textStatus) {
        var json = WST.toJson(data);
        console.log(json);
        var html = new Array();
        $("#otbody" + statusMark).empty();
        var tmpMsg = '';
        if (json.root.length > 0) {
            for (var i = 0; i < json.root.length; i++) {
                var order = json.root[i];
                html.push("<tr style='color:blue'>");
                html.push("<td width='20'><input type='checkbox' class='chk_" + statusMark + "' value='" + order.orderId + "'/></td>");
                html.push("<td width='100'><a href='javascript:;' style='color:blue' font-weight:bold;' onclick=showOrder('" + order.orderId + "')>" + order.orderNo + "</a></td>");
                var orderType = '首充号';
                if (order.orderType == 1) {
                    orderType = '首充号';
                    if (order.goodsType == 1) {
                        orderType = '会员首充';
                    }
                } else if (order.orderType == 2) {
                    orderType = '首充号代充';
                    if (order.goodsType == 1) {
                        orderType = '会员首代';
                    }
                }

                html.push("<td width='100'>" + orderType + "</td>");
                html.push("<td width='100'>" + order.goodsName + "</td>");
                html.push("<td width='100'>" + order.gameName + "</td>");
                html.push("<td width='100'>" + order.vName + "</td>");
                html.push("<td width='100'>" + order.goodsNums + "</td>");
                html.push("<td width='100'>" + order.needPay + "</td>");
                html.push("<td width='100'>" + order.createTime + "</td>");
                html.push("<td width='100'>");
                html.push("<a href='javascript:;' style='color:" + ((order.orderStatus == -6 || order.orderStatus == -3 || order.orderStatus == -7) ? "red" : "blue") + "' onclick=showOrder('" + order.orderId + "')>查看</a>");
                if (order.orderStatus == 1) {
                    html.push(" | <a href='javascript:;' style='color:" + ((order.orderStatus == -6 || order.orderStatus == -3) ? "red" : "blue") + "' onclick=fahuo('" + order.orderId + "')>手动发货</a>");
                    
                    /**
                     * @author peng
                     * @copyright 2016
                     * @remark
                     */
                    if (order.vName=='手游狗版本')
                    html.push(" | <a href='javascript:;' style='color:" + ((order.orderStatus == -6 || order.orderStatus == -3) ? "red" : "blue") + "' onclick=auto_fahuo('" + order.orderId + "')>一键发货</a>");
                }
                html.push("</td>");
                html.push("</tr>");
            }
            $("#otbody" + statusMark).html(html.join(""));
        }
        layer.close(ll);
        if (json.totalPage > 1) {
            laypage({
                cont: "wst-page-" + statusMark,
                pages: json.totalPage,
                curr: json.currPage,
                skin: '#e23e3d',
                groups: 3,
                jump: function (e, first) {
                    if (!first) {
                        queryOrderPager(statusMark, e.curr);
                    }
                }
            });

        } else {
            $('#wst-page-' + statusMark).remove();
        }
        var hh = $('.wst-content').height();
        $('.wst-menu').css('min-height', hh + 'px');
    });
}
function queryAuctionOrderPager(statusMark, pcurr, id, skId) {
    var param = {};
    param.orderNo = $.trim($("#orderNo_" + statusMark).val());
    param.userName = $.trim($("#userName_" + statusMark).val());
    param.userAddress = $.trim($("#userAddress_" + statusMark).val());
    param.statusMark = statusMark;
    param.pcurr = pcurr;
    //团购活动id
    param.id = arguments[2] ? arguments[2] : 0;
    param.skId = arguments[3] ? arguments[3] : 0;
    var ll = layer.load('数据加载中，请稍候...');
    $.post(Think.U('Home/Orders/queryShopAuctionOrders'), param, function (data, textStatus) {
        var json = WST.toJson(data);
        var html = new Array();
        $("#otbody" + statusMark).empty();
        var tmpMsg = '';
        if (json.root.length > 0) {
            for (var i = 0; i < json.root.length; i++) {
                var order = json.root[i];
                html.push("<tr style='color:" + ((order.orderStatus == -6 || order.orderStatus == -3) ? "red" : "blue") + ";'>");
                if (order.orderStatus == 0 || order.orderStatus == 1 || order.orderStatus == 2) {
                    html.push("<td width='20'><input type='checkbox' class='chk_" + order.orderStatus + "' value='" + order.orderId + "'/></td>");
                }
                html.push("<td width='100'><a href='javascript:;' style='color:" + ((order.orderStatus == -6 || order.orderStatus == -3) ? "red" : "blue") + ";font-weight:bold;' onclick=showOrder('" + order.orderId + "')>" + order.orderNo + "</a></td>");
                html.push("<td width='100'>" + order.userName + "</td>");
                if (order.orderStatus <= -3 && order.orderStatus >= -7 || (order.orderStatus == -1)) {
                    html.push("<td width='*' title='" + order.rejectionRemarks + "'>" + WST.cutStr(order.rejectionRemarks, 40) + "</td>");
                } else if (order.orderStatus == 6 || order.orderStatus == 7) {
                    html.push("<td width='*'>" + order.lastTime + "</td>");
                } else {
                    html.push("<td width='*'>" + order.userAddress + "</td>");
                }
                html.push("<td width='100'>" + order.totalMoney + "</td>");
                html.push("<td width='100'><div style='line-height:20px;'>" + order.createTime + "</div></td>");
                html.push("<td width='100'>");
                html.push("<a href='javascript:;' style='color:" + ((order.orderStatus == -6 || order.orderStatus == -3) ? "red" : "blue") + "' onclick=showOrder('" + order.orderId + "')>查看</a>");
                if (order.orderStatus == 0) {
                    html.push(" | <a href='javascript:;' style='color:" + ((order.orderStatus == -6 || order.orderStatus == -3) ? "red" : "blue") + "' onclick=shopOrderAccept('" + order.orderId + "')>受理</a>");
                    if (id) {
                        html.push(" | <a href='javascript:;' style='color:" + ((order.orderStatus == -6 || order.orderStatus == -3) ? "red" : "blue") + "' onclick=shopOrderCancel('" + order.orderId + "','" + order.orderStatus + "','" + order.shopId + "')>取消</a>");
                    }
                } else if (order.orderStatus == 1) {
                    html.push(" | <a href='javascript:;' style='color:" + ((order.orderStatus == -6 || order.orderStatus == -3) ? "red" : "blue") + "' onclick=shopOrderProduce('" + order.orderId + "')>打包</a>");
                } else if (order.orderStatus == 2) {
                    html.push(" | <a href='javascript:;' style='color:" + ((order.orderStatus == -6 || order.orderStatus == -3) ? "red" : "blue") + "' onclick=shopOrderDeliveryType('" + order.orderId + "')>发货配送</a>");
                } else if (order.orderStatus == -3) {
                    html.push(" | <a href='javascript:;' style='color:" + ((order.orderStatus == -6 || order.orderStatus == -3) ? "red" : "blue") + "' onclick=shopOrderRefund('" + order.orderId + "',1)>同意拒收</a>");
                    html.push(" | <a href='javascript:;' style='color:" + ((order.orderStatus == -6 || order.orderStatus == -3) ? "red" : "blue") + "' onclick=shopOrderRefund('" + order.orderId + "',-1)>不同意拒收</a>");
                }
                html.push("</td>");
                html.push("</tr>");
            }
            $("#otbody" + statusMark).html(html.join(""));
        }
        layer.close(ll);
        if (json.totalPage > 1) {
            laypage({
                cont: "wst-page-" + statusMark,
                pages: json.totalPage,
                curr: json.currPage,
                skin: '#e23e3d',
                groups: 3,
                jump: function (e, first) {
                    if (!first) {
                        queryOrderPager(statusMark, e.curr);
                    }
                }
            });
        } else {
            $('#wst-page-' + statusMark).remove();
        }

    });
}
/*******修改密码 ********************/
function editPass() {
    var params = {};
    params.oldPass = $('#oldPass').val();
    params.newPass = $('#newPass').val();
    params.reNewPass = $('#reNewPass').val();
    $.post(Think.U('Home/Users/editPass'), params, function (data, textStatus) {
        var json = WST.toJson(data);
        if (json.status == '1') {
            WST.msg('密码修改成功!', {icon: 1}, function () {
                location.reload();
            });
        } else if (json.status == '-2') {
            WST.msg('原始密码不正确!', {icon: 5});
        } else {
            WST.msg('密码修改失败!', {icon: 5});
        }
    });
}
/***************编辑店铺资料******************/
function getCommunitysForShopEdit() {
    return;

    $.post(Think.U('Home/Areas/getAreaAndCommunitysByList'), {areaId: areaId}, function (data, textStatus) {
        var json = data;
        if (json.list) {

            var html = [];
            json = json.list;
            for (var i = 0; i < json.length; i++) {
                var isAreaSelected = ($.inArray(json[i]['areaId'], relateArea) > -1) ? " checked " : "";
                communitysCount = 0
                if (json[i].communitys) {
                    for (var j = json[i].communitys.length - 1; j >= 0; j--) {
                        if ($.inArray(json[i].communitys[j]['communityId'], relateCommunity) > -1) {
                            communitysCount++;
                        }
                        ;
                    }
                    ;
                }
                ;
                html.push("<dl class='areaSelect' id='" + json[i]['areaId'] + "'>");
                html.push("<dt class='ATRoot' id='node_" + json[i]['areaId'] + "' isshow='0'>" + json[i]['areaName'] + "：<span> <input " + ((isSelf == 1) ? "disabled" : "") + " type='checkbox' all='1' class='AreaNode' onclick='javascript:selectArea(this)' id='ck_" + json[i]['areaId'] + "' " + isAreaSelected + " value='" + json[i]['areaId'] + "'><label for='ck_" + json[i]['areaId'] + "' " + isAreaSelected + " value='" + json[i]['areaId'] + "'>全区配送</label></span> <small>(已选<span class='count'>" + communitysCount + "</span>个社区)</small></dt>");
                if (json[i].communitys && json[i].communitys.length) {
                    for (var j = 0; j < json[i].communitys.length; j++) {
                        var isCommunitySelected = ($.inArray(json[i].communitys[j]['communityId'], relateCommunity) > -1) ? " checked " : "";
                        isCommunitySelected += (isAreaSelected != '') ? " disabled " : "";
                        html.push("<dd id='node_" + json[i]['areaId'] + "_" + json[i].communitys[j]['communityId'] + "'><input " + ((isSelf == 1) ? "disabled" : "") + " type='checkbox' id='ck_" + json[i]['areaId'] + "_" + json[i].communitys[j]['communityId'] + "' all='0' class='AreaNode' " + isCommunitySelected + " onclick='javascript:selectArea(this)' value='" + json[i].communitys[j]['communityId'] + "'><label for='ck_" + json[i]['areaId'] + "_" + json[i].communitys[j]['communityId'] + "'>" + json[i].communitys[j]['communityName'] + "</label></dd>");
                    }
                }
                html.push("</dl>");
            }
            $('#areaTree').html(html.join(''));
            $('#expendAll').parent().removeClass('Hide');
            $('#expendAll').attr('checked', 'checked');
        }
    });
}
function selectArea(v) {
    var count = 0;
    if ($(v).attr('all') == '1') {
        $('input[id^="' + $(v).attr('id') + '_"]').each(function () {
            $(this)[0].checked = $(v)[0].checked;
            $(this)[0].disabled = $(v)[0].checked;
            if ($(v)[0].checked) {
                count++
            }
            ;
        });
    } else {
        $(v).closest('dl').find('input[type="checkbox"]').each(function () {
            if ($(this).prop('checked') == true) {
                count++
            }
            ;
        });
    }
    $(v).closest('dl').find('.count:first').html(count);
}
function initTime(objId, val) {
    for (var i = 0; i < 24; i++) {
        $('<option value="' + i + '" ' + ((val == i) ? "selected" : '') + '>' + i + ':00</option>').appendTo($('#' + objId));
        $('<option value="' + (i + ".5") + '" ' + ((val == (i + ".5")) ? "selected" : '') + '>' + i + ':30</option>').appendTo($('#' + objId));
    }
}
function isInvoce(v) {
    if (v) {
        $('#invoiceRemarkstr').show();
    } else {
        $('#invoiceRemarkstr').hide();
    }
}
function editShop() {
    var params = {};
    params.userName = $('#userName').val();
    params.shopName = $('#shopName').val();
    params.shopImg = $('#shopImg').val();
    params.serviceStartTime = $('#serviceStartTime').val();
    params.serviceEndTime = $('#serviceEndTime').val();
    params.bankId = $('#bankId').val();
    params.bankNo = $('#bankNo').val();
    params.agentStatus = $("input[name='agentStatus']:checked").val();
    var scope = '';
    $(".scope").each(function () {
        if ($(this).is(":checked")) {
            scope += $(this).val() + ',';
        }
    });
    scope = scope.substring(0, scope.length - 1);
    params.scope = String(scope);
    if (parseInt(params.serviceStartTime, 10) > parseInt(params.serviceEndTime, 10)) {
        WST.msg('开始时间不能大于结束时间!', {icon: 2}, function () {
        });
        return;
    }
    var layerIdx = layer.load('正在处理，请稍后...', 3);
    $.post(Think.U('Home/Shops/edit'), params, function (data, textStatus) {
        var json = WST.toJson(data);
        layer.close(layerIdx);
        if (json.status == '1') {
            WST.msg('操作成功!', {icon: 1}, function () {
                //location.href = location.href;
            });
        } else {
            WST.msg('操作失败!', {icon: 5});
        }
    });
}
/******************店铺设置************************/
function setShop() {
    var params = {};
    params.shopTitle = $('#shopTitle').val();
    params.shopKeywords = $('#shopKeywords').val();
    params.shopBanner = $('#shopBanner').val();
    var shopAds = new Array();
    var shopAdsUrl = new Array();
    $('.gallery-img').each(function () {
        shopAds.push($(this).attr("v"));
    });
    $('.gallery-img-url').each(function () {
        shopAdsUrl.push($(this).val());
    });
    params.shopAds = shopAds.join('#@#');
    params.shopAdsUrl = shopAdsUrl.join('#@#');
    params.shopDesc = $('#shopDesc').val();
    layer.load('正在处理，请稍后...', 3);

    $.post(Think.U('Home/Shops/editShopCfg'), params, function (data, textStatus) {
        var json = WST.toJson(data);
        if (json.status == '1') {
            WST.msg('操作成功!', {icon: 1}, function () {
                location.href = location.href;
            });
        } else {
            WST.msg('操作失败!', {icon: 5});
        }
    });
}
function logout() {
    jQuery.post(Think.U('Home/Shops/logout'), {}, function (rsp) {
        location.reload();
    });
}
function checkLogin() {
    jQuery.post(Think.U('Home/Shops/checkLoginStatus'), {}, function (rsp) {
        var json = WST.toJson(rsp);
        if (json.status && json.status == -999)location.reload();
    });
}
/***************物流管理****************/
function editExpress(type, src) {
    var params = {};
    params.expressCompany = $.trim($('#expressCompany').val());
    if (params.expressCompany == '') {
        WST.msg('请输入物流名称!', {icon: 5});
        return;
    }
    params.telephone = $.trim($('#telephone').val());
    if (params.telephone == '') {
        WST.msg('请输入查询电话!', {icon: 5});
        return;
    }
    params.website = $.trim($('#website').val());
    if (params.website == '') {
        WST.msg('请输入官方网址!', {icon: 5});
        return;
    }
    params.isShow = $('input[name="isShow"]:checked').val();
    params.umark = src;
    params.id = $('#id').val();
    var loading = layer.load('正在处理，请稍后...', 3);
    $.post(Think.U('Home/Express/edit'), params, function (data, textStatus) {
        layer.close(loading);
        var json = WST.toJson(data);
        if (json.status == '1') {
            WST.msg('操作成功!', {icon: 1}, function () {
                if (type == 1) {
                    $('#myform')[0].reset();
                } else {
                    location.href = Think.U('Home/Express/index');
                }
            });
        } else {
            WST.msg('操作失败!', {icon: 5});
        }
    });
}
function delExpress(id) {
    layer.confirm("您确定要删除该物流信息吗？", {icon: 3, title: '系统提示'}, function (tips) {
        var loading = layer.load('正在处理，请稍后...', 3);
        layer.close(tips);
        var params = {};
        $.post(Think.U('Home/Express/del'), {id: id}, function (data, textStatus) {
            layer.close(loading);
            var json = WST.toJson(data);
            if (json.status == '1') {
                WST.msg('操作成功！', {icon: 1}, function () {
                    location.reload();
                });
            } else {
                WST.msg('操作失败!', {icon: 5});
            }
        });
    });
}
/***************商品类型****************/
function editAttrCats(type, src) {
    var catName = $.trim($('#catName').val());
    if (catName == '') {
        WST.msg('请输入商品类型名称!', {icon: 5});
        return;
    }
    var loading = layer.load('正在处理，请稍后...', 3);
    $.post(Think.U('Home/AttributeCats/edit'), {
        umark: src,
        catName: catName,
        id: $('#id').val()
    }, function (data, textStatus) {
        layer.close(loading);
        var json = WST.toJson(data);
        if (json.status == '1') {
            WST.msg('操作成功!', {icon: 1}, function () {
                if (type == 1) {
                    $('#myform')[0].reset();
                } else {
                    location.href = Think.U('Home/AttributeCats/index');
                }
            });
        } else {
            WST.msg('操作失败!', {icon: 5});
        }
    });
}
function delAttrCat(id) {
    layer.confirm("您确定要删除该商品类型及其下的属性吗？", {icon: 3, title: '系统提示'}, function (tips) {
        var loading = layer.load('正在处理，请稍后...', 3);
        layer.close(tips);
        var params = {};
        $.post(Think.U('Home/AttributeCats/del'), {id: id}, function (data, textStatus) {
            layer.close(loading);
            var json = WST.toJson(data);
            if (json.status == '1') {
                WST.msg('操作成功！', {icon: 1}, function () {
                    location.reload();
                });
            } else {
                WST.msg('操作失败!', {icon: 5});
            }
        });
    });
}
/***********商品属性************/
function getAttrsForAttr() {
    location.href = Think.U("Home/Attributes/index", 'catId=' + $('#catId').val());
}
function toAddAttr() {
    var attrNoForAttr = $('#catId').attr('dataNo');
    attrNoForAttr++
    var html = [];
    html.push("<tr id='tr_" + attrNoForAttr + "' dataId='0'><td>&nbsp;</td>");
    html.push("<td><input type='text' id='attrName_" + attrNoForAttr + "'/></td>");
    html.push("<td style='display:none;'><input type='checkbox'  checked disabled  name='isPriceAttr' id='isPriceAttr_" + attrNoForAttr + "' onclick='javascript:chkPriceAttrForAttr()' id='isPriceAttr_" + attrNoForAttr + "'></td>");
    //html.push("<td><select id='attrType_"+attrNoForAttr+"' onchange='javascript:changeAttrTypeForAttr("+attrNoForAttr+")'><option value='0'>输入框</option/><option value='1'>多选项</option/><option value='2'>下拉项</option/></select>");
    // html.push("</td>");
    //html.push("<td><input type='text' id='attrContent_"+attrNoForAttr+"' style='width:300px;display:none'/></td>");
    html.push("<td><input type='text' id='attrSort_" + attrNoForAttr + "'/></td>");
    html.push("<td>");
    html.push("<a href='javascript:delAttrs(" + attrNoForAttr + ",0)' class='btn del' title='删除'></a>");
    html.push("</td>");
    html.push("</tr>");
    $('#tbody').append(html.join(''));
    $('#catId').attr('dataNo', attrNoForAttr);
    $('.wst-btn-query').show();
}
function changeAttrTypeForAttr(v) {
    if ($('#attrType_' + v).val() == 0) {
        $('#attrContent_' + v).hide();
    } else {
        $('#attrContent_' + v).show();
    }
}
function chkPriceAttrForAttr() {
    var attrNoForAttr = $('#catId').attr('dataNo');
    for (var i = 0; i <= attrNoForAttr; i++) {
        if (!document.getElementById('tr_' + i))continue;
        if ($('#isPriceAttr_' + i)[0].checked) {
            $('#attrType_' + i).hide();
            $('#attrContent_' + i).hide();
        } else {
            $('#attrType_' + i).show();
            if ($('#attrType_' + i).val() == 1 || $('#attrType_' + i).val() == 2) {
                $('#attrContent_' + i).show();
            }
        }
    }
}
function editAttrs() {
    var attrNoForAttr = $('#catId').attr('dataNo');
    var params = {}
    params.catId = $('#catId').val();
    params.no = attrNoForAttr;
    for (var i = 0; i <= attrNoForAttr; i++) {
        if (!document.getElementById('tr_' + i))continue;
        params['id_' + i] = $('#tr_' + i).attr('dataId');
        var isPriceAttr = $('#isPriceAttr_' + i)[0].checked ? 1 : 0;
        params['isPriceAttr_' + i] = isPriceAttr;
        params['attrName_' + i] = $.trim($('#attrName_' + i).val());
        if (params['attrName_' + i] == '') {
            WST.msg('请输入属性名称!', {icon: 5});
            $('#attrName_' + i).focus();
            return;
        }
        params['attrType_' + i] = $('#attrType_' + i).val();
        params['attrContent_' + i] = $.trim($('#attrContent_' + i).val());
        if ((params['attrType_' + i] == 1 || params['attrType_' + i] == 2) && isPriceAttr == 0 && params['attrContent_' + i] == '') {
            WST.msg('请输入属性选项值!', {icon: 5});
            $('#attrContent_' + i).focus();
            return;
        }
        params['attrSort_' + i] = $.trim($('#attrSort_' + i).val());
    }

    var loading = layer.load('正在处理，请稍后...', 3);
    $.post(Think.U('Home/attributes/edit'), params, function (data, textStatus) {
        layer.close(loading);
        var json = WST.toJson(data);
        if (json.status == '1') {
            WST.msg('操作成功!', {icon: 1}, function () {
                location.href = Think.U('Home/Attributes/index', 'catId=' + $('#catId').val());
            });
        } else {
            WST.msg('操作失败!', {icon: 5});
        }
    });
}
function delAttrs(no, id) {
    if (id > 0) {
        layer.confirm("您确定要删除该商品属性吗？", {icon: 3, title: '系统提示'}, function (tips) {
            var loading = layer.load('正在处理，请稍后...', 3);
            layer.close(tips);
            var params = {};
            $.post(Think.U('Home/Attributes/del'), {id: id}, function (data, textStatus) {
                layer.close(loading);
                var json = WST.toJson(data);
                if (json.status == '1') {
                    WST.msg('操作成功！', {icon: 1}, function () {
                        location.reload();
                    });
                } else {
                    WST.msg('操作失败!', {icon: 5});
                }
            });
        });
    } else {
        $('#tr_' + no).remove();
    }
}
function batchMessageDel() {
    layer.confirm("您确定要删除这些消息？", function () {
        var ids = getChks();
        layer.load('正在处理，请稍后...', 3);
        var params = {};
        params.ids = ids;
        $.post(Think.U('Home/Messages/batchDel'), params, function (data, textStatus) {
            var json = WST.toJson(data);
            if (json.status == '1') {
                WST.msg('操作成功！', {icon: 1}, function () {
                    location.reload();
                });
            } else {
                WST.msg('操作失败', {icon: 5});
            }
        });
    });
}


function toEditGoodsBase(fv, goodsId, flag) {
    if ((fv == 2 || fv == 3) && flag == 1) {
        WST.msg('该商品存在商品属性，不能直接修改，请进入编辑页修改', {icon: 5});
        return;
    } else {
        $("#ipt_" + fv + "_" + goodsId).show();
        $("#span_" + fv + "_" + goodsId).hide();
        $("#ipt_" + fv + "_" + goodsId).focus();
        $("#ipt_" + fv + "_" + goodsId).val($("#span_" + fv + "_" + goodsId).html());
    }

}

function endEditGoodsBase(fv, goodsId) {
    $('#span_' + fv + '_' + goodsId).html($('#ipt_' + fv + '_' + goodsId).val());
    $('#span_' + fv + '_' + goodsId).show();
    $('#ipt_' + fv + '_' + goodsId).hide();
}

function editGoodsBase(fv, goodsId) {
    var vtext = $('#ipt_' + fv + '_' + goodsId).val();
    if ($.trim(vtext) == '') {
        if (fv == 1) {
            WST.msg('商品编号不能为空', {icon: 5});
        } else if (fv == 2) {
            WST.msg('价格不能为空', {icon: 5});
        } else if (fv == 3) {
            WST.msg('库存不能为空', {icon: 5});
        }
        return;
    }
    jQuery.post(Think.U('Home/Goods/editGoodsBase'), {
        vfield: fv,
        goodsId: goodsId,
        vtext: vtext
    }, function (data, textStatus) {
        var json = WST.toJson(data);
        if (json.status > 0) {
            $('#img_' + fv + '_' + goodsId).fadeTo("fast", 100);
            endEditGoodsBase(fv, goodsId);
            $('#img_' + fv + '_' + goodsId).fadeTo("slow", 0);
        } else {
            WST.msg('修改失败!', {icon: 5});
        }
    });
}

function changeCatStatus(isShow, id, pid) {
    var params = {};
    params.id = id;
    params.isShow = isShow;
    params.pid = pid;
    $.post(Think.U('Home/ShopsCats/changeCatStatus'), params, function (data, textStatus) {
        location.reload();
    });

}
function changeExpressStatus(isShow, id, pid) {
    var params = {};
    params.id = id;
    params.isShow = isShow;
    params.pid = pid;
    $.post(Think.U('Home/Express/changeExpressStatus'), params, function (data, textStatus) {
        var json = WST.toJson(data);
        location.reload();
    });

}


function changSaleStatus(goodsId, flag) {
    var tak = "";
    if (flag == 1) {
        tak = "isRecomm";
    } else if (flag == 2) {
        tak = "isBest";
    } else if (flag == 3) {
        tak = "isNew";
    } else if (flag == 4) {
        tak = "isHot";
    } else if (flag == 5) {
        tak = "isSale";
    }
    var tamk = $("#" + tak + "_" + goodsId).val();
    jQuery.post(Think.U('Home/Goods/changSaleStatus'), {
        goodsId: goodsId,
        tamk: tamk,
        flag: flag
    }, function (data, textStatus) {
        var json = WST.toJson(data);
        if (json.status > 0) {
            if (tamk == 0) {
                tamk = 1;
                $("#" + tak + "_div_" + goodsId).html("<span class='wst-state_yes'></span>");
            } else {
                tamk = 0;
                $("#" + tak + "_div_" + goodsId).html("<span class='wst-state_no'></span>");
            }
            $("#" + tak + "_" + goodsId).val(tamk);


        } else {
            WST.msg('修改失败!', {icon: 5});
        }
    });
}
function addGoodsCat(obj, p, catNo) {
    var html = new Array();
    if (typeof(obj) == "number") {
        html.push("<tbody class='tbody_new'>");
        html.push("<tr class='tr_new' isLoad='1'>");
        html.push("<td>");
        html.push("<span class='wst-tree-open'>&nbsp;</span>");
        html.push("<input class='catname' type='text' style='width:400px;height:22px;margin-left:6px;' dataId=''/>");
        html.push("</td>");
        html.push("<td><input class='catsort' type='text' style='width:35px;' value='0' onkeyup='javascript:WST.isChinese(this,1)' onkeypress='return WST.isNumberKey(event)'/></td>");
        html.push("<td style='cursor:pointer;'><input class='catshow' type='checkbox'/></td>");
        html.push("<td>");
        html.push("<span onclick='addGoodsCat(this,0,0);' class='add btn' title='新增'></span>");
        html.push("<span class='del btn' title='删除' onclick='delGoodsCatObj(this,1)'></span>&nbsp;");
        html.push("</td>");
        html.push("</tr>");
        html.push("</tbody>");
        $("#cat_list_tab").append(html.join(""));
    } else {
        var className = (p == 0) ? "tr_c_new" : "tr_" + catNo + " tr_0";
        html.push("<tr class='" + className + "' isLoad='1' catId='" + p + "'>");
        html.push("<td>");
        html.push("<span class='wst-tree-second'>&nbsp;</span>&nbsp;");
        html.push("<input class='catname' type='text' style='width:400px;height:22px;' dataId=''/>");
        html.push("</td>");
        html.push("<td><input class='catsort' type='text' style='width:35px;' value='0' onkeyup='javascript:WST.isChinese(this,1)' onkeypress='return WST.isNumberKey(event)'/></td>");
        html.push("<td style='cursor:pointer;' ><input class='catshow' type='checkbox' /></td>");
        html.push("<td>");
        html.push("<span class='del btn' title='删除' onclick='delGoodsCatObj(this,2)'></span>&nbsp;");
        html.push("</td>");
        html.push("</tr>");
        $(obj).parent().parent().parent().append(html.join(""))
    }
    $('.wst-btn-query').show();
}

function batchSaveShopCats() {
    var params = {};
    var fristNo = 0;
    var secondNo = 0;
    $(".tbody_new").each(function () {
        secondNo = 0;
        var pobj = $(this).find(".tr_new");
        params['catName_' + fristNo] = $.trim(pobj.find(".catname").val());
        if (params['catName_' + fristNo] == '') {
            WST.msg('请输入商品分类名称!', {icon: 5});
            return;
        }
        params['catSort_' + fristNo] = pobj.find(".catsort").val();
        params['catShow_' + fristNo] = pobj.find(".catshow").prop("checked") ? 1 : 0
        $(this).find(".tr_c_new").each(function () {
            params['catId_' + fristNo + '_' + secondNo] = fristNo;
            params['catName_' + fristNo + '_' + secondNo] = $.trim($(this).find(".catname").val());
            if (params['catName_' + fristNo + '_' + secondNo] == '') {
                WST.msg('请输入商品分类名称!', {icon: 5});
                return;
            }
            params['catSort_' + fristNo + '_' + secondNo] = $(this).find(".catsort").val();
            params['catShow_' + fristNo + '_' + secondNo] = $(this).find(".catshow").prop("checked") ? 1 : 0
            params['catSecondNo_' + fristNo] = ++secondNo;
        });
        params['fristNo'] = ++fristNo;
    });
    var otherNo = 0;
    $(".tr_0").each(function () {
        params['catId_o_' + otherNo] = $(this).attr('catId');
        params['catName_o_' + otherNo] = $.trim($(this).find(".catname").val());
        if (params['catName_o_' + otherNo] == '') {
            WST.msg('请输入商品分类名称!', {icon: 5});
            return;
        }
        params['catSort_o_' + otherNo] = $(this).find(".catsort").val();
        params['catShow_o_' + otherNo] = $(this).find(".catshow").prop("checked") ? 1 : 0;
        params['otherNo'] = ++otherNo;
    });
    $.post(Think.U('Home/ShopsCats/batchSaveShopCats'), params, function (data, textStatus) {
        var json = WST.toJson(data);
        if (json.status == 1) {
            WST.msg('新增成功!', {icon: 1, time: 500}, function () {
                location.reload();
            });
        } else {
            WST.msg('新增失败!', {icon: 5});
        }
    });
}

function delGoodsCatObj(obj, vk) {
    if (vk == 1) {
        $(obj).parent().parent().parent().remove();
    } else {
        $(obj).parent().parent().remove();
    }
    if ($(".tr_0").size() == 0 && $(".tbody_new").size() == 0)$('.wst-btn-query').hide();
}

function getShopMsgTips() {
    /**
     * @author peng	
     * @date 2017-01-04
     * @descreption 加上平台的情况
     */
    $.post(Think.U('Home/Orders/getShopMsgTips','is_pt='+is_pt), {}, function (data, textStatus) {
        var json = WST.toJson(data);
        for (var i in json) {
            if (json[i] > 0) {
                //待处理订单
                if (json['0'] > 0) {
                    $("#li_toShopOrdersList .wst-msg-tips-box").html(json['0']);
                    $("#li_toShopOrdersList .wst-msg-tips-box").show();
                }
                if (json['100000'] > 0) {
                    $("#li_queryMessageByPage .wst-msg-tips-box").html(json['100000']);
                    $("#li_queryMessageByPage .wst-msg-tips-box").show();
                }
                if (json['refund'] > 0) {
                    $('.refundCnt').text(json['refund']);
                    $('.refundCnt').show();
                } else {
                    $('.refundCnt').hide();
                }

                $("#wst-msg-li-" + i + " .wst-order-tips-box").show();
            } else {
                $("#wst-msg-li-" + i + " .wst-order-tips-box").hide();
            }
            $("#wst-msg-li-" + i + " .wst-order-tips-box").html(json[i]);
        }
    });
}

$(function () {
    getShopMsgTips();
    setInterval("getShopMsgTips()", 30000);
    
    $('body').on('click','.is_renyi',function(){
        var shopPrice = $(this).closest('td').find('#shopPrice');
        if(!$(this).prop('checked')){
            shopPrice.prop('disabled',false);
        }else{
            shopPrice.prop('disabled',true);
        }
    })
});
